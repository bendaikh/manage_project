import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';

// Create Vue app
const app = createApp(App);

// Mount the app
app.mount('#app');
