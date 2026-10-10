<div class="xzoom-container product-gallery">
    @php $externalGallery = $product->externalGalleryUrls(); @endphp
    @if ($images->isEmpty() && count($externalGallery))
        <img class="@if ($product->is_downloadable) downloadable_image @else xzoom5 @endif" id="xzoom-magnific" src="{{ $externalGallery[0] }}" xoriginal="{{ $externalGallery[0] }}" alt="{{ $product->name }}" />
        <div class="d-none">
            @foreach ($externalGallery as $imageUrl)
                <img class="zoom-image" src="{{ $imageUrl }}" data-zoom-image="{{ $imageUrl }}" alt="{{ $product->name }} image">
            @endforeach
        </div>
        @if (count($externalGallery) > 1)
            <div class="xzoom-thumbs">
                <div class="quick-view-slider owl-carousel owl-theme">
                    @foreach ($externalGallery as $index => $imageUrl)
                        <a href="{{ $imageUrl }}" class="media-item-nav" data-media_id="external-{{ $index }}">
                            <img class="@if ($product->is_downloadable) downloadable_gallery @else xzoom-gallery5 @endif" width="80" src="{{ $imageUrl }}" xpreview="{{ $imageUrl }}" alt="{{ $product->name }} image {{ $index + 1 }}">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <img class="@if ($product->is_downloadable) downloadable_image @else xzoom5 @endif" id="xzoom-magnific" src="{{ $images->first()?->full_url ?? asset('assets/images/default.png') }}" xoriginal="{{ $images->first()?->full_url }}" alt="{{ $product->name }}" />
        <div class="d-none">
            @foreach ($images as $image)
                <img class="zoom-image" src="{{ $image->full_url }}" data-zoom-image="{{ $image->full_url }}" alt="Image {{ $loop->iteration }}">
            @endforeach
        </div>

        @if ($images->isNotEmpty())
            <div class="xzoom-thumbs">
                <div class="quick-view-slider owl-carousel owl-theme">
                    @foreach ($images as $image)
                        <a href="{{ $image->full_url }}" class="media-item-nav" data-media_id="{{ $image->id }}">
                            <img class="@if ($product->is_downloadable) downloadable_gallery @else xzoom-gallery5 @endif" width="80" src="{{ getImage($image->path . '/thumb_' . $image->file_name) }}" xpreview="{{ $image->full_url }}" alt="{{ $product->name }} image {{ $loop->iteration }}">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
