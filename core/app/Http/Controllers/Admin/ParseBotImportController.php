<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frontend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class ParseBotImportController extends Controller
{
    private const SETTINGS_KEY = 'parsebot_admin_settings';
    private const DAILY_BUDGET = 250;
    private const MAX_RUN_BUDGET = 5;

    public function index()
    {
        $settings = Frontend::where('data_keys', self::SETTINGS_KEY)->first();
        $values = (array) ($settings?->data_values ?? []);
        $today = now()->toDateString();
        $used = ($values['credit_date'] ?? null) === $today ? (int) ($values['reserved_credits'] ?? 0) : 0;

        return view('admin.product.parsebot_import', [
            'environmentKeyConfigured' => trim((string) config('services.parsebot.api_key')) !== '',
            'fallbackKeyConfigured' => !empty($values['encrypted_api_key']),
            'creditsReservedToday' => min(self::DAILY_BUDGET, $used),
            'dailyBudget' => self::DAILY_BUDGET,
            'maxRunBudget' => self::MAX_RUN_BUDGET,
        ]);
    }

    public function saveFallbackKey(Request $request)
    {
        $request->validate(['api_key' => ['required', 'string', 'min:8', 'max:500']]);
        $settings = Frontend::where('data_keys', self::SETTINGS_KEY)->first() ?? new Frontend();
        $settings->data_keys = self::SETTINGS_KEY;
        $values = (array) ($settings->data_values ?? []);
        $values['encrypted_api_key'] = Crypt::encryptString(trim($request->input('api_key')));
        $settings->data_values = (object) $values;
        $settings->save();

        return back()->with('success', 'Encrypted fallback key saved. The deployment environment key remains authoritative when configured.');
    }

    public function run(Request $request)
    {
        $validated = $request->validate([
            'mode' => ['required', Rule::in(['listings', 'details', 'stored', 'categories'])],
            'credit_limit' => ['required', 'integer', 'min:1', 'max:' . self::MAX_RUN_BUDGET],
            'category_ids' => ['nullable', 'string', 'max:2000', 'regex:/^[0-9,\\s]*$/'],
            'all_pages' => ['nullable', 'boolean'],
        ]);

        $mode = $validated['mode'];
        $limit = (int) $validated['credit_limit'];
        if ($mode === 'listings' && !trim((string) ($validated['category_ids'] ?? '')) && empty($validated['all_pages'])) {
            return back()->withErrors(['category_ids' => 'Select source category IDs or enable all saved categories.']);
        }

        if (in_array($mode, ['listings', 'details'], true)
            && !config('services.parsebot.api_key')
            && !$this->fallbackKeyIsConfigured()) {
            return back()->withErrors(['api_key' => 'Configure a deployment API key or save the encrypted fallback first.']);
        }

        try {
            if (in_array($mode, ['listings', 'details'], true)) {
                $this->reserveDailyBudget($limit);
            }

            $options = [
                '--credit-limit' => $limit,
                '--all-pages' => $mode === 'listings' && !empty($validated['all_pages']),
                '--details-only' => $mode === 'details',
                '--sync-stored-details' => $mode === 'stored',
                '--categories-only' => $mode === 'categories',
                '--listings-only' => $mode === 'listings',
            ];
            if (trim((string) ($validated['category_ids'] ?? '')) !== '') {
                $options['--category-ids'] = preg_replace('/\s+/', '', $validated['category_ids']);
            }

            $exitCode = Artisan::call('parsebot:import-catalog', $options);
            $output = mb_substr(Artisan::output(), 0, 12000);

            return back()
                ->with('parsebot_output', $output)
                ->with($exitCode === 0 ? 'success' : 'error', $exitCode === 0 ? 'Import step finished.' : 'Import step stopped; review the output and resume after correcting the issue.');
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['import' => 'The import step failed. Check the server log; the API key is not included in the error message.']);
        }
    }

    private function fallbackKeyIsConfigured(): bool
    {
        $settings = Frontend::where('data_keys', self::SETTINGS_KEY)->first();
        $encrypted = data_get($settings?->data_values, 'encrypted_api_key');
        if (!is_string($encrypted) || $encrypted === '') {
            return false;
        }
        try {
            return Crypt::decryptString($encrypted) !== '';
        } catch (Throwable) {
            return false;
        }
    }

    private function reserveDailyBudget(int $limit): void
    {
        DB::transaction(function () use ($limit) {
            $settings = Frontend::where('data_keys', self::SETTINGS_KEY)->lockForUpdate()->first();
            $values = (array) ($settings?->data_values ?? []);
            $today = now()->toDateString();
            $used = ($values['credit_date'] ?? null) === $today ? (int) ($values['reserved_credits'] ?? 0) : 0;
            if ($used + $limit > self::DAILY_BUDGET) {
                throw new \RuntimeException('The daily Parse.bot request budget is fully reserved.');
            }
            $values['credit_date'] = $today;
            $values['reserved_credits'] = $used + $limit;
            if (!$settings) {
                $settings = new Frontend();
                $settings->data_keys = self::SETTINGS_KEY;
            }
            $settings->data_values = (object) $values;
            $settings->save();
        });
    }
}
