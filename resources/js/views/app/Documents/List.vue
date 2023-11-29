<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { useI18n } from 'vue-i18n'
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { useToast } from 'primevue/usetoast';
    import { appStore } from '@/store.js'
    
    export default {
        setup() {
            setMetaTitle('meta.title.documents_list')
            
            const router = useRouter()
            const { t } = useI18n();
            const toast = useToast();
            
            return {
                router,
                t,
                toast,
                hasAccess
            }
        },
        data() {
            return {
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('menu.estates'), disabled : true },
                        {'label' : this.t('menu.documents'), disabled : true },
                    ],
                }
            }
        },
        mounted() {
        },
        methods: {
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
</template>