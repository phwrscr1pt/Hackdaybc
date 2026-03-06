const express = require('express');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

const articles = [
  { id: 1, title: 'welcome to this blog', summary: 'enjoy!' },
  { id: 2, title: 'how to print hello world', summary: 'help' },
  { id: 3, title: 'meowmeowcat92', summary: 'meowmeowmeow' },
  { id: 4, title: 'six seven', summary: '67' },
];

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

function sanitize(input) {
  return input
    .replace(/<script>/gi, '')
    .replace(/<\/script>/gi, '');
}

app.get('/', (req, res) => {
  res.render('index', { articles });
});

app.get('/search', (req, res) => {
  const raw = req.query.q || '';
  const query = sanitize(raw);
  const results = articles.filter(a =>
    a.title.toLowerCase().includes(raw.toLowerCase())
  );
  res.render('search', { query, results });
});

app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
});
