<script setup>
import { ref } from 'vue';

import MenuItem from './MenuItem.vue';
import { useI18n } from 'vue-i18n'
const { t } = useI18n();

const model = ref([
    {
        label: t('app.home'),
        items: [{ label: t('app.dashboard'), icon: 'pi pi-fw pi-home', to: { name: 'dashboard' } }]
    },
    {
        label: t('app.estates'),
        items: [
            { label: t('app.estate_list'), icon: 'pi pi-fw pi-building', to: { name: 'users' } },
        ]
    },
    {
        label: t('app.users'),
        items: [
            { label: t('app.users_list'), icon: 'pi pi-fw pi-users', to: { name: 'users' } },
            { label: t('app.permissions'), icon: 'pi pi-fw pi-key', to: { name: 'permissions' } },
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

