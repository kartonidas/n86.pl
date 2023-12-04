<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    export default {
        setup() {
            setMetaTitle('meta.title.faults_list')
            
            const router = useRouter()
            
            return {
                router,
                hasAccess
            }
        },
        data() {
            return {
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.faults'), disabled : true },
                    ],
                }
            }
        },
        methods: {
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
</template>