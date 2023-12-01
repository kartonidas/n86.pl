<script>
    import { useRoute, useRouter } from 'vue-router'
    import { useToast } from 'primevue/usetoast';
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf } from '@/utils/i18n-validators'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import ItemForm from './_Form.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { ItemForm },
        setup() {
            setMetaTitle('meta.title.items_edit')
            
            const route = useRoute()
            const router = useRouter()
            const itemService = new ItemService()
            const toast = useToast();
            
            return {
                v$: useVuelidate(),
                itemService,
                route,
                router,
                toast
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                errors: [],
                item: {},
                settings: {
                    showCustomers: false,
                },
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.estate_list'), route : { name : 'items'} }, 
                        {'label' : this.$t('items.edit'), disabled : true },
                    ],
                }
            }
        },
        mounted() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
            
            this.itemService.get(this.route.params.itemId)
                .then(
                    (response) => {
                        this.item = response.data
                        this.loading = false
                    },
                    (response) => {
                        this.toast.add({ severity: 'error', summary: this.$t('app.error'), detail: response.response.data.message, life: 3000 });
                    }
                );
                
            this.itemService.settings()
                .then(
                    (response) => {
                        this.settings.types = response.data.types
                        this.settings.showCustomers = response.data.customer_access
                        this.settings.customers = this.settings.showCustomers ? response.data.customers : []
                    },
                    (response) => {
                        this.errors = getResponseErrors(response)
                    }
                );
        },
        methods: {
            async updateItem() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.itemService.update(this.route.params.itemId, this.item)
                        .then(
                            (response) => {
                                this.toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.updated'), life: 3000 });
                                this.saving = false;
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                    )
                }
            },
            
            back() {
                this.router.push({name: 'item_show', params: { itemId : this.item.id }})
            }
        },
        validations () {
            return {
                item: {
                    name: { required },
                    type: { required },
                    street: { required },
                    city: { required },
                    zip: { required },
                    customer_id: { required: requiredIf(function() { return !this.item.ownership }) },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid">
                <h4 class="mb-5 header-border-bottom pb-2">{{ $t('items.basic_data') }}</h4>
                <form v-on:submit.prevent="updateItem">
                    <ItemForm :item="item" :settings="settings" :saving="saving" :loading="loading" :errors="errors" :v="v$" />
                    
                    <div v-if="loading">
                        <ProgressSpinner style="width: 25px; height: 25px"/>
                    </div>
                    
                    <div class="flex justify-content-between align-items-center">
                        <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                        <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>