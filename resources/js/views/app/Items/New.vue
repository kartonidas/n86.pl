<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import ItemForm from './_Form.vue'
    import CustomerForm from './../Customers/_Form.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { ItemForm, CustomerForm },
        setup() {
            setMetaTitle('meta.title.items_new')
            
            const itemService = new ItemService()
            return {
                itemService,
            }
        },
        data() {
            return {
                errors: [],
                item : {
                    ownership_type: 'property',
                    type: 'apartment',
                    country : 'PL',
                },
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.estates'), disabled : true },
                        {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                        {'label' : this.$t('items.new_item'), disabled : true },
                    ],
                },
                saving: false,
                fromCustomer: false
            }
        },
        mounted() {
            if(this.$route.name == "item_new_customer")
            {
                this.item.ownership_type = 'manage'
                this.item.customer_id = parseInt(this.$route.params.customerId)
                this.fromCustomer = true
            }
        },
        methods: {
            async createItem() {
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
                            
                            if(this.$route.name == "item_new_customer")
                                this.$router.push({name: 'customer_show', params: { customerId : this.$route.params.customerId }})
                            else
                                this.$router.push({name: 'item_show', params: { itemId : response.data }})
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
            
            setErrors(errors) {
                this.errors = errors
            }
        }
    }
</script>
    
<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid pt-4">
                <Help show="item" class="text-right mb-3"/>
                <h4 class="mb-5 header-border-bottom pb-2 text-color font-medium">{{ $t('items.new_item') }}</h4>
                <ItemForm @submit-form="createItem" @set-errors="setErrors" :item="item" :saving="saving" :errors="errors" :fromCustomer="fromCustomer"/>
            </div>
        </div>
    </div>
</template>