<script>
    import { appStore } from '@/store.js'
    import Page404 from '@/layout/404.vue'
    
    export default {
        components: { Page404 },
        beforeMount() {
            if(appStore().error404) {
                let m = appStore().error404
                this.errorPage = m;
            }
        },
        data() {
            return {
                errorPage : ""
            }
        }
    }
</script>

<template>
    <Page404 :error="errorPage"/>
</template>
    