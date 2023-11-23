<script setup>
import { ref } from 'vue';

import MenuItem from './MenuItem.vue';
import { useI18n } from 'vue-i18n'
const { t } = useI18n();
import { hasAccess } from '@/utils/helper.js'

const model = ref([
    {
        label: t('app.home'),
        items: [{ label: t('app.dashboard'), icon: 'pi pi-fw pi-home', to: { name: 'dashboard' } }]
    },
    {
        label: t('app.estates'),
        access: hasAccess('item:list'),
        items: [
            { label: t('app.estate_list'), icon: 'pi pi-fw pi-building', to: { name: 'items' } },
            { label: t('app.tenant_list'), icon: 'pi pi-fw pi-user', to: { name: 'tenants' } },
            { label: t('app.documents'), icon: 'pi pi-fw pi-file', to: { name: 'documents' } },
            { label: t('app.faults'), icon: 'pi pi-fw pi-wrench', to: { name: 'faults' } },
        ]
    },
    {
        label: t('app.users'),
        access: hasAccess('user:list') || hasAccess('permission:list'),
        items: [
            { label: t('app.users_list'), icon: 'pi pi-fw pi-users', to: { name: 'users' }, access: hasAccess('user:list') },
            { label: t('app.permissions'), icon: 'pi pi-fw pi-key', to: { name: 'permissions' }, access: hasAccess('permission:list') },
        ]
    },
    {
        label: t('app.settings'),
        access: hasAccess('dictionary:list'),
        items: [
            {
                label: t('app.dictionaries'),
                icon: 'pi pi-fw pi-book',
                access: hasAccess('dictionary:list'),
                items: [
                    { label: t('app.fee_include_rent'), icon: 'pi pi-fw pi-list', to: { name: 'dictionaries', params: {type:'fees'} } },
                    { label: t('app.bill_type'), icon: 'pi pi-fw pi-list', to: { name: 'dictionaries', params: {type:'bills'} } },
                ]
            },
            { label: t('app.configuration'), icon: 'pi pi-fw pi-cog', to: { name: 'config' } },
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

