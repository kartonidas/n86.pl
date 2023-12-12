<script setup>
    import { ref } from 'vue'
    import Breadcrumb from 'primevue/breadcrumb';
    import { useI18n } from 'vue-i18n'
    
    const { t } = useI18n();
    const props = defineProps({
        model: {
            type: Array,
            default: () => ([])
        },
    });
    
    const breadcrumbHome = { 'label' : t('menu.home'), 'route' : { name : 'dashboard' } };
</script>

<template>
    <Breadcrumb :home="breadcrumbHome" :model="model">
        <template #item="{ item, props }">
            <router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
                <a :href="href" v-bind="props.action" @click="navigate" class="p-menuitem-link">
                    <span class="text-color">{{ item.label }}</span>
                </a>
            </router-link>
            <a v-else :href="item.url" :target="item.target" v-bind="props.action">
                <span class="text-color">{{ item.label }}</span>
            </a>
        </template>
    </Breadcrumb>
</template>