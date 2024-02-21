<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import TabMenu from './../_TabMenu.vue'
    import BillForm from './_Form.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { BillForm, TabMenu },
        props: {
            item: { type: Object },
        },
        setup() {
            setMetaTitle('meta.title.items_update_bill')
            
            const itemService = new ItemService()
            return {
                itemService,
            }
        },
        data() {
            return {
                loading: true,
                errors: [],
                bill : {},
                saving: false,
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null);
            }
            
            this.itemService.getBill(this.$route.params.itemId, this.$route.params.billId)
                .then(
                    (response) => {
                        this.bill = response.data
                        if(this.bill.payment_date && !(this.bill.payment_date instanceof Date))
                            this.bill.payment_date = new Date(this.bill.payment_date)
                            
                        if(this.bill.source_document_date && !(this.bill.source_document_date instanceof Date))
                            this.bill.source_document_date = new Date(this.bill.source_document_date)
                        
                        this.loading = false
                    },
                    (errors) => {
                        if(errors.response.status == 404)
                        {
                            appStore().setError404(errors.response.data.message);
                            this.$router.push({name: 'objectnotfound'})
                        }
                        else
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
                    items.push({'label' : this.$t('items.bills'), route : { name : 'item_bills'} })
                    items.push({'label' : this.$t('items.update_bill'), disabled : true })
                }
                    
                return items
            },
            
            async updateBill(bill) {
                this.saving = true
                this.errors = []
                
                this.itemService.updateBill(this.$route.params.itemId, this.$route.params.billId, bill)
                    .then(
                        (response) => {
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.bill_updated'), life: 3000 });
                            this.saving = false;
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
            
            back() {
                this.$goBack('item_bill_show');
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card pt-4">
                <Help show="item" class="text-right mb-3"/>
                <TabMenu activeIndex="fees:bills" :item="item" class="mb-5" :showEditButton="false" :showDivider="true"/>
                <BillForm @submit-form="updateBill" @back="back" :bill="bill" source="update" :saving="saving" :loading="loading" :errors="errors" />
            </div>
        </div>
    </div>
</template>