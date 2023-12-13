<script>
    import ItemService from '@/service/ItemService'
    import Page404 from '@/layout/404.vue'
    
    export default {
        components: { Page404 },
        setup() {
            const itemService = new ItemService()
            return {
                itemService
            }
        },
        data() {
            return {
                item: {},
                loading: true,
                errorPage: false
            }
        },
        beforeMount() {
            this.itemService.get(this.$route.params.itemId)
                .then(
                    (response) => {
                        this.item = response.data
                        this.loading = false
                    },
                    (errors) => {
                        this.errorPage = errors.response.data.message
                    }
                );
        },
    }
</script>

<template>
    <Page404 v-if="errorPage" :error="errorPage" :back="{ name: 'items' }"/>
    <router-view :item="item" v-if="!loading"></router-view>
</template>