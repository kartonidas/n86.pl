import './bootstrap';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createHead } from 'unhead';

import App from './App.vue'
import router from './router';
import i18n from "./i18n";
import globalDirectives from './directives';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import VueNumerals from 'vue-numerals';

import PrimeVue from 'primevue/config';
import AppBreadcrumb from '@/layout/app/AppBreadcrumb.vue';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Card from 'primevue/card';
import Column from 'primevue/column';
import Checkbox from 'primevue/checkbox';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Divider from 'primevue/divider';
import Dropdown from 'primevue/dropdown';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import InputMask from 'primevue/inputmask';
import InputNumber from 'primevue/inputnumber';
import InputSwitch from 'primevue/inputswitch';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Password from 'primevue/password';
import ProgressSpinner from 'primevue/progressspinner';
import RadioButton from 'primevue/radiobutton';
import Sidebar from 'primevue/sidebar';
import Steps from 'primevue/steps';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip';

axios.defaults.baseURL = 'https://estate.netextend.pl/';
axios.defaults.params = {
    '_locale': i18n.global.locale.value
}

import '@/assets/styles.scss';

const app = createApp(App)
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
createHead()

app.config.globalProperties.rowsPerPage = 25;
app.use(i18n);
app.use(router);
app.use(globalDirectives);
app.use(pinia);
app.use(PrimeVue);
app.use(ToastService);
app.use(VueNumerals, { locale: i18n.global.locale.value });

app.component('Breadcrumb', AppBreadcrumb);
app.component('Button', Button);
app.component('Calendar', Calendar);
app.component('Card', Card);
app.component('Checkbox', Checkbox);
app.component('Column', Column);
app.component('DataTable', DataTable);
app.component('Dialog', Dialog);
app.component('Divider', Divider);
app.component('Dropdown', Dropdown);
app.component('InputGroup', InputGroup);
app.component('InputMask', InputMask);
app.component('InputNumber', InputNumber);
app.component('InputSwitch', InputSwitch);
app.component('InputText', InputText);
app.component('Message', Message);
app.component('Password', Password);
app.component('ProgressSpinner', ProgressSpinner);
app.component('RadioButton', RadioButton);
app.component('Sidebar', Sidebar);
app.component('Steps', Steps);
app.component('TabView', TabView);
app.component('TabPanel', TabPanel);
app.component('Textarea', Textarea);
app.component('Toast', Toast);
app.directive('tooltip', Tooltip);


app.mount("#app");