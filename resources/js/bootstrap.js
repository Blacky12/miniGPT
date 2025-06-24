import axios from 'axios';
window.axios = axios;

// Indispensable pour que Laravel accepte la suppression de la conversation
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
