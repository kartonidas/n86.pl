<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import TabMenu from './../_TabMenu.vue'
    import BillForm from './_Form.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { BillForm, TabMenu },
        setup() {
            setMetaTitle('meta.title.items_new_bill')
            
            const itemService = new ItemService()
            return {
                itemService,
            }
        },
        data() {
            return {
                errors: [],
                bill : {},
                item: {},
                saving: false,
                fromCustomer: false
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
                        this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                );
        },
        methods: {
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                ]
                
                if(this.item.name != undefined)
                {
                    items.push({'label' : this.item.name, route : { name : 'item_show'} })
                    items.push({'label' : this.$t('items.bills'), disabled : true })
                    items.push({'label' : this.$t('items.new_bill'), disabled : true })
                }
                    
                return items
            },
            
            async createBill(bill) {
                this.saving = true
                this.errors = []
                
                this.itemService.createBill(this.$route.params.itemId, bill)
                    .then(
                        (response) => {
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('items.bill_added'),
                            });
                            
                            this.$router.push({name: 'item_bill_edit', params: { itemId : this.$route.params.itemId, billId : response.data }})
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <TabMenu activeIndex="fees:bills" :item="item" class="mb-5" :showEditButton="false" :showDivider="true"/>
                <BillForm @submit-form="createBill" :bill="bill" :saving="saving" :errors="errors" />
            </div>
        </div>
    </div>
</template>