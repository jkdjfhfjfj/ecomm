const http = require("node:http");

const listenPort = Number(process.env.PORT);
const upstreamPort = Number(process.env.ECOMM_UPSTREAM_PORT || 5000);

if (!Number.isInteger(listenPort) || listenPort < 1 || listenPort > 65535) {
  throw new Error("PORT must be set to a valid port for the storefront preview proxy.");
}

const server = http.createServer((request, response) => {
  const upstream = http.request(
    {
      hostname: "127.0.0.1",
      port: upstreamPort,
      method: request.method,
      path: request.url,
      headers: {
        ...request.headers,
        host: request.headers.host || `127.0.0.1:${upstreamPort}`,
      },
    },
    (upstreamResponse) => {
      response.writeHead(upstreamResponse.statusCode || 502, upstreamResponse.headers);
      upstreamResponse.pipe(response);
    },
  );

  upstream.on("error", () => {
    if (!response.headersSent) {
      response.writeHead(502, { "content-type": "text/plain; charset=utf-8" });
    }
    response.end("The legacy Ecomm Storefront workflow is not responding on port 5000.");
  });

  request.pipe(upstream);
});

server.listen(listenPort, "0.0.0.0", () => {
  console.log(`Ecomm root preview proxy listening on 0.0.0.0:${listenPort}; forwarding to 127.0.0.1:${upstreamPort}`);
});
