<script setup>
import db from '@/utils/firebase'
import { useDatabaseList } from 'vuefire'
import { ref as dbRef } from 'firebase/database'
import { getAuth, signInWithEmailAndPassword } from 'firebase/auth'
import { appStore } from '@/store.js'
import Package from './../../views/app/_partials/Package.vue';

import { ref } from 'vue';

import MenuItem from './MenuItem.vue';
import { useI18n } from 'vue-i18n'
const { t } = useI18n();
import { hasAccess } from '@/utils/helper.js'

let counters = ref({});
try {
    const auth = getAuth();
    if (appStore().firebase) {
        let firebaseCredentials = appStore().firebase.split("@");
        let login = firebaseCredentials[0];
        let pass = firebaseCredentials[1];
        
        signInWithEmailAndPassword(auth, login + "@n86.pl", pass)
            .then(
                () => {},
                () => {},
            );
            
        counters = useDatabaseList(dbRef(db, 'stats/' + firebaseCredentials[0]));
    }
} catch (e) {}



const model = ref([
    {
        label: t('menu.home'),
        items: [{ label: t('menu.dashboard'), icon: 'pi pi-fw pi-home', to: { name: 'dashboard' }, regex: /^\/app$/i }]
    },
    {
        label: t('menu.estates'),
        access: hasAccess('item:list') || hasAccess('rent:list') || hasAccess('customer:list') || hasAccess('tenant:list') || hasAccess('document:list') || hasAccess('fault:list'),
        items: [
            {
                label: t('menu.estate_list'),
                icon: 'pi pi-fw pi-building',
                to: { name: 'items' },
                access: hasAccess('item:list'),
                regex: /^\/app\/item(s?)(\/(?!archived).*)?$/i,
                count: [{'index' : 'items', 'class' : 'info'}]
            },
            {
                label: t('menu.rentals_list'),
                icon: 'pi pi-fw pi-dollar',
                to: { name: 'rentals' },
                access: hasAccess('rent:list'),
                regex: /^\/app\/(rental(s?)|rent)(\/(.*))?$/i,
                count: [{'index' : 'rentals', 'class' : 'info'}]
            },
            {
                label: t('menu.customer_list'),
                icon: 'pi pi-fw pi-briefcase',
                to: { name: 'customers' },
                access: hasAccess('customer:list'),
                regex: /^\/app\/customer(s?)(\/(.*))?$/i,
                count: [{'index' : 'customers', 'class' : 'info'}]
            },
            {
                label: t('menu.tenant_list'),
                icon: 'pi pi-fw pi-user',
                to: { name: 'tenants' },
                access: hasAccess('tenant:list'),
                regex: /^\/app\/tenant(s?)(\/(.*))?$/i,
                count: [{'index' : 'tenants', 'class' : 'info'}]
            },
            {
                label: t('menu.documents'),
                icon: 'pi pi-fw pi-file',
                to: { name: 'documents' },
                access: hasAccess('document:list'),
                regex: /^\/app\/document(s?)(\/(?!templates).*)?$/i
            },
            {
                label: t('menu.faults'),
                icon: 'pi pi-fw pi-wrench',
                to: { name: 'faults' },
                access: hasAccess('fault:list'),
                regex: /^\/app\/fault(s?)(\/(.*))?$/i
            },
        ]
    },
    {
        label: t('menu.users'),
        access: hasAccess('user:list') || hasAccess('permission:list'),
        items: [
            { label: t('menu.users_list'), icon: 'pi pi-fw pi-users', to: { name: 'users' }, access: hasAccess('user:list'), regex: /^\/app\/user(s?)(\/(?!permission|permissions|config).*)?$/i },
            { label: t('menu.permissions'), icon: 'pi pi-fw pi-key', to: { name: 'permissions' }, access: hasAccess('permission:list'), regex: /^\/app\/user\/permission(s?)(\/(.*))?$/i },
        ]
    },
    {
        label: t('menu.settlements'),
        access: hasAccess('config:update') || hasAccess('customer_invoices:list'),
        items: [
            { label: t('menu.customer_invoices'), icon: 'pi pi-fw pi-file-pdf', to: { name: 'customer_invoices' }, access: hasAccess('customer_invoices:list'), regex: /^\/app\/customer\-invoices(\/(?!sale-register|config).*)?$/i },
            { label: t('menu.sale_registries'), icon: 'pi pi-fw pi-folder', to: { name: 'sale_register' }, access: hasAccess('config:update'), regex: /^\/app\/customer\-invoices\/sale-register(\/(.*))?$/i },
            { label: t('menu.customer_invoices_config'), icon: 'pi pi-fw pi-cog', to: { name: 'customer_invoices_config' }, access: hasAccess('config:update'), regex: /^\/app\/customer\-invoices\/config(\/(.*))?$/i },
        ]
    },
    {
        label: t('menu.settings'),
        access: hasAccess('dictionary:list') || hasAccess('config:update') || hasAccess('owner'),
        items: [
            {
                label: t('menu.dictionaries'),
                icon: 'pi pi-fw pi-book',
                access: hasAccess('dictionary:list'),
                items: [
                    { label: t('menu.bill_type'), icon: 'pi pi-fw pi-list', to: { name: 'dictionaries', params: {type:'bills'} }, regex: /^\/app\/dictionary\/bill(s?)(\/(.*))?$/i },
                    { label: t('menu.payment_types'), icon: 'pi pi-fw pi-list', to: { name: 'dictionaries', params: {type:'payment_types'} }, regex: /^\/app\/dictionary\/payment_types(s?)(\/(.*))?$/i },
                    { label: t('menu.fault_statuses'), icon: 'pi pi-fw pi-wrench', to: { name: 'dictionaries', params: {type:'fault_statuses'} }, regex: /^\/app\/dictionary\/fault_statuses(s?)(\/(.*))?$/i },
                ]
            },
            { label: t('menu.document_templates'), icon: 'pi pi-fw pi-file-edit', to: { name: 'documents_templates' }, access: hasAccess('config:update'), regex: /^\/app\/documents\/templates(\/(.*))?$/i },
            { label: t('menu.configuration'), icon: 'pi pi-fw pi-cog', to: { name: 'config' }, access: hasAccess('config:update'), regex: /^\/app\/user\/config$/i },
            { label: t('menu.firm_data'), icon: 'pi pi-fw pi-wallet', access: hasAccess('owner'), to: { name: 'firm_data' }, regex: /^\/app\/firm\-data(\/(.*))?$/i},
        ]
    },
    {
        label: t('menu.finances'),
        access: hasAccess('owner'),
        items: [
            { label: t('menu.invoices'), icon: 'pi pi-fw pi-chart-bar', to: { name: 'invoices' }, access: hasAccess('owner'), regex: /^\/app\/invoices(\/(.*))?$/i },
        ]
    },
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <menu-item v-if="!item.separator" :item="item" :index="i" :counters="counters"></menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
    <Package location="header"/>
</template>

