<script setup>
import { ref } from 'vue';

import MenuItem from './MenuItem.vue';
import { useI18n } from 'vue-i18n'
const { t } = useI18n();
import { hasAccess } from '@/utils/helper.js'

const model = ref([
    {
        label: t('menu.home'),
        items: [{ label: t('menu.dashboard'), icon: 'pi pi-fw pi-home', to: { name: 'dashboard' }, regex: /^\/app$/i }]
    },
    {
        label: t('menu.estates'),
        access: hasAccess('item:list'),
        items: [
            { label: t('menu.estate_list'), icon: 'pi pi-fw pi-building', to: { name: 'items' }, regex: /^\/app\/item(s?)(\/(.*))?$/i },
            { label: t('menu.rentals_list'), icon: 'pi pi-fw pi-dollar', to: { name: 'rentals' }, regex: /^\/app\/(rental(s?)|rent)(\/(.*))?$/i },
            { label: t('menu.customer_list'), icon: 'pi pi-fw pi-briefcase', to: { name: 'customers' }, regex: /^\/app\/customer(s?)(\/(.*))?$/i },
            { label: t('menu.tenant_list'), icon: 'pi pi-fw pi-user', to: { name: 'tenants' }, regex: /^\/app\/tenant(s?)(\/(.*))?$/i },
            { label: t('menu.documents'), icon: 'pi pi-fw pi-file', to: { name: 'documents' }, regex: /^\/app\/document(s?)(\/(?!templates).*)?$/i },
            { label: t('menu.faults'), icon: 'pi pi-fw pi-wrench', to: { name: 'faults' }, regex: /^\/app\/fault(s?)(\/(.*))?$/i },
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
        label: t('menu.settings'),
        access: hasAccess('dictionary:list'),
        items: [
            {
                label: t('menu.dictionaries'),
                icon: 'pi pi-fw pi-book',
                access: hasAccess('dictionary:list'),
                items: [
                    { label: t('menu.fee_include_rent'), icon: 'pi pi-fw pi-list', to: { name: 'dictionaries', params: {type:'fees'} }, regex: /^\/app\/dictionary\/fee(s?)(\/(.*))?$/i },
                    { label: t('menu.bill_type'), icon: 'pi pi-fw pi-list', to: { name: 'dictionaries', params: {type:'bills'} }, regex: /^\/app\/dictionary\/bill(s?)(\/(.*))?$/i },
                ]
            },
            { label: t('menu.document_templates'), icon: 'pi pi-fw pi-file-edit', to: { name: 'documents_templates' }, regex: /^\/app\/documents\/templates(\/(.*))?$/i },
            { label: t('menu.configuration'), icon: 'pi pi-fw pi-cog', to: { name: 'config' }, regex: /^\/app\/user\/config$/i },
            { label: t('menu.firm_data'), icon: 'pi pi-fw pi-wallet', access: hasAccess('owner'), to: { name: 'firm_data' } , regex: /^\/app\/firm\-data(\/(.*))?$/i},
        ]
    },
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <menu-item v-if="!item.separator" :item="item" :index="i"></menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

