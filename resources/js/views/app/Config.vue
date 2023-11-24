<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    
    export default {
        setup() {
            const { t } = useI18n();
            
            return {
                t,
            }
        },
        data() {
            return {
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('menu.settings'), disabled : true },
                        {'label' : this.t('menu.configuration'), disabled : true },
                    ],
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
</template>