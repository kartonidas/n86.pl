<script setup>
import { ref } from 'vue';

import MenuItem from './MenuItem.vue';
import { useI18n } from 'vue-i18n'
const { t } = useI18n();
import { hasAccess } from '@/utils/helper.js'

const model = ref([
    {
        label: t('menu.home'),
        items: [{ label: t('menu.dashboard'), icon: 'pi pi-fw pi-home', to: { name: 'dashboard' } }]
    },
    {
        label: t('menu.estates'),
        access: hasAccess('item:list'),
        items: [
            { label: t('menu.estate_list'), icon: 'pi pi-fw pi-building', to: { name: 'items' } },
            { label: t('menu.customer_list'), icon: 'pi pi-fw pi-briefcase', to: { name: 'customers' } },
            { label: t('menu.tenant_list'), icon: 'pi pi-fw pi-user', to: { name: 'tenants' } },
            { label: t('menu.documents'), icon: 'pi pi-fw pi-file', to: { name: 'documents' } },
            { label: t('menu.faults'), icon: 'pi pi-fw pi-wrench', to: { name: 'faults' } },
        ]
    },
    {
        label: t('menu.users'),
        access: hasAccess('user:list') || hasAccess('permission:list'),
        items: [
            { label: t('menu.users_list'), icon: 'pi pi-fw pi-users', to: { name: 'users' }, access: hasAccess('user:list') },
            { label: t('menu.permissions'), icon: 'pi pi-fw pi-key', to: { name: 'permissions' }, access: hasAccess('permission:list') },
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
                    { label: t('menu.fee_include_rent'), icon: 'pi pi-fw pi-list', to: { name: 'dictionaries', params: {type:'fees'} } },
                    { label: t('menu.bill_type'), icon: 'pi pi-fw pi-list', to: { name: 'dictionaries', params: {type:'bills'} } },
                ]
            },
            { label: t('menu.configuration'), icon: 'pi pi-fw pi-cog', to: { name: 'config' } },
            { label: t('menu.firm_data'), icon: 'pi pi-fw pi-wallet', access: hasAccess('owner'), to: { name: 'firm_data' } },
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

