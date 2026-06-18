#!/usr/bin/env python3
"""
Local dev server that mirrors the .htaccess clean-URL behaviour.

Why this exists:
    python3 -m http.server cannot interpret .htaccess rewrite rules. On the
    live Apache host the rules in .htaccess do the job; locally we need a
    tiny shim so that visiting /apps actually serves apps.html.

Usage:
    cd ~/Desktop/qubi-website
    python3 dev-server.py            # listens on http://localhost:8000

To stop: Ctrl+C.
"""

import http.server
import socketserver
import sys
from urllib.parse import urlsplit, urlunsplit

PORT = 8000

# Clean URL → real file. Keep this in sync with .htaccess.
ROUTES = {
    "/":                            "/index.html",
    "/apps":                        "/apps.html",
    "/about":                       "/about.html",
    "/support":                     "/support.html",
    "/contact":                     "/contact.html",
    "/wondertale":                  "/wondertale.html",
    "/wondertale/privacy-policy":   "/wondertale-privacy.html",
    "/wondertale/terms":            "/wondertale-terms.html",
    "/purl":                        "/purl/index.html",
    "/purl/privacy":               "/purl/privacy.html",
    "/purl/terms":                 "/purl/terms.html",
    "/purl/support":               "/purl/support.html",
}

# 301 redirects: legacy paths → current clean URL.
LEGACY_REDIRECTS = {
    "/apps.html":               "/apps",
    "/about.html":              "/about",
    "/support.html":            "/support",
    "/contact.html":            "/contact",
    "/wondertale.html":         "/wondertale",
    "/wondertale-privacy.html": "/wondertale/privacy-policy",
    "/wondertale-terms.html":   "/wondertale/terms",
    # Old site-wide legal URLs from the previous structure.
    "/privacy-policy":          "/wondertale/privacy-policy",
    "/terms":                   "/wondertale/terms",
}


class Handler(http.server.SimpleHTTPRequestHandler):
    def do_GET(self):
        parts = urlsplit(self.path)
        path = parts.path
        normalized = path.rstrip("/") or "/"

        if normalized in LEGACY_REDIRECTS:
            new_path = LEGACY_REDIRECTS[normalized]
            target = urlunsplit(("", "", new_path, parts.query, parts.fragment))
            self.send_response(301)
            self.send_header("Location", target)
            self.end_headers()
            return

        if normalized in ROUTES:
            real = ROUTES[normalized]
            self.path = urlunsplit(("", "", real, parts.query, parts.fragment))

        return super().do_GET()


def main():
    with socketserver.TCPServer(("", PORT), Handler) as httpd:
        print(f"Serving on http://localhost:{PORT}  (Ctrl+C to stop)")
        print("Clean URLs:")
        for clean in ROUTES:
            print(f"  http://localhost:{PORT}{clean}")
        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            print("\nBye.")
            sys.exit(0)


if __name__ == "__main__":
    main()
