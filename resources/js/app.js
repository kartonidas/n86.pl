import './bootstrap';

import { createApp } from 'vue'

import App from './App.vue'
import router from './router';
import store from './store.js'
import i18n from "./i18n"

import PrimeVue from 'primevue/config';

import AppBreadcrumb from '@/layout/app/AppBreadcrumb.vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import Checkbox from 'primevue/checkbox';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Password from 'primevue/password';
import ProgressSpinner from 'primevue/progressspinner';
import Sidebar from 'primevue/sidebar';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';

axios.defaults.baseURL = 'https://estate.netextend.pl/';
axios.defaults.params = {
    '_locale': 'pl'
}

import '@/assets/styles.scss';

const app = createApp(App)

app.config.globalProperties.rowsPerPage = 25;

app.use(i18n)
app.use(router);
app.use(store)
app.use(PrimeVue);
app.use(ToastService);

app.component('Breadcrumb', AppBreadcrumb);
app.component('Button', Button);
app.component('Checkbox', Checkbox);
app.component('Column', Column);
app.component('DataTable', DataTable);
app.component('Dialog', Dialog);
app.component('Dropdown', Dropdown);
app.component('InputText', InputText);
app.component('Message', Message);
app.component('Password', Password);
app.component('ProgressSpinner', ProgressSpinner);
app.component('Sidebar', Sidebar);
app.component('Toast', Toast);

app.mount("#app")