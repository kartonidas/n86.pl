<script>
    import { ref } from 'vue'
    import { setMetaTitle } from '@/utils/helper'
    
    export default {
        setup() {
            setMetaTitle('meta.title.config')
            
            return { }
        },
        data() {
            return {
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.settings'), disabled : true },
                        {'label' : this.$t('menu.configuration'), disabled : true },
                    ],
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
</template>