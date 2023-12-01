<script>
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf } from '@/utils/i18n-validators'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import ItemForm from './_Form.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { ItemForm },
        setup() {
            setMetaTitle('meta.title.items_new')
            
            const router = useRouter()
            const itemService = new ItemService()
            
            return {
                v$: useVuelidate(),
                itemService,
                router
            }
        },
        data() {
            return {
                saving: false,
                item : {
                    ownership: true,
                    country : 'PL'
                },
                settings: {
                    showCustomers: false,
                },
                errors: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                        {'label' : this.$t('items.new_item'), disabled : true },
                    ],
                }
            }
        },
        mounted() {
            this.itemService.settings()
                .then(
                    (response) => {
                        this.settings.types = response.data.types
                        this.item.type = response.data.default_type
                        this.settings.showCustomers = response.data.customer_access
                        this.settings.customers = this.settings.showCustomers ? response.data.customers : []
                    },
                    (response) => {
                        this.errors = getResponseErrors(response)
                    }
                );
        },
        methods: {
            async createItem() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    this.itemService.create(this.item)
                        .then(
                            (response) => {
                                appStore().setToastMessage({
                                    severity : 'success',
                                    summary : this.$t('app.success'),
                                    detail : this.$t('items.added'),
                                });
                                
                                this.router.push({name: 'item_show', params: { itemId : response.data }})
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                        )
                }
            },
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
                <form v-on:submit.prevent="createItem">
                    <ItemForm :item="item" :settings="settings" :saving="saving" :errors="errors" :v="v$" />
                    
                    <div class="text-right">
                        <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>