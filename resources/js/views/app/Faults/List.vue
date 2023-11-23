<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { useI18n } from 'vue-i18n'
    import { hasAccess } from '@/utils/helper'
    import { useToast } from 'primevue/usetoast';
    import store from '@/store.js'
    
    export default {
        setup() {
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
                        {'label' : this.t('app.estates'), disabled : true },
                        {'label' : this.t('app.faults'), disabled : true },
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