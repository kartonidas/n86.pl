<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useRoute } from 'vue-router'
    import { useToast } from 'primevue/usetoast';
    import { getResponseErrors } from '@/utils/helper'
    import store from '@/store.js'
    
    import ItemService from '@/service/ItemService'
    
    export default {
        setup() {
            const route = useRoute()
            const itemService = new ItemService()
            const { t } = useI18n();
            const toast = useToast();
            
            return {
                t,
                itemService,
                route,
                toast
            }
        },
        data() {
            return {
                errors: [],
                item: {},
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('app.items'), disabled : true },
                        {'label' : this.t('app.estate_list'), route : { name : 'items'} },
                        {'label' : this.t('app.edit_item'), disabled : true },
                    ],
                }
            }
        },
        mounted() {
            this.itemService.get(this.route.params.itemId)
                .then(
                    (response) => {
                        this.item = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.toast.add({ severity: 'error', summary: this.t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
</template>