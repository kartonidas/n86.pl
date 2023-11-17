import './bootstrap';

import { createApp } from 'vue'

import App from './App.vue'
import router from './router';
import store from './store.js'
import i18n from "./i18n"

import PrimeVue from 'primevue/config';
import Breadcrumb from 'primevue/breadcrumb';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Password from 'primevue/password';
import Sidebar from 'primevue/sidebar';

axios.defaults.baseURL = 'https://estate.netextend.pl/';
axios.defaults.params = {
    '_locale': 'pl'
}

import '@/assets/styles.scss';

const app = createApp(App)
app.use(i18n)
app.use(router);
app.use(store)
app.use(PrimeVue);

app.component('Breadcrumb', Breadcrumb);
app.component('Button', Button);
app.component('InputText', InputText);
app.component('Message', Message);
app.component('Password', Password);
app.component('Sidebar', Sidebar);

app.mount("#app")