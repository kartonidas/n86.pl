<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { setMetaTitle } from '@/utils/helper'
    
    export default {
        setup() {
            setMetaTitle('meta.title.dashboard')
            
            const { t } = useI18n();
            
            return {
                t,
            }
        },
        data() {
            return {
            }
        },
    }
</script>

<template>
    Tablica
</template>