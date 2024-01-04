<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle, hasAccess } from '@/utils/helper'
    import moment from 'moment'
    
    import TabMenu from './../_TabMenu.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { TabMenu },
        props: {
            item: { type: Object },
        },
        setup() {
            setMetaTitle('meta.title.items_show_bill')
            
            const itemService = new ItemService()
            return {
                itemService,
                hasAccess,
            }
        },
        data() {
            return {
                loading: true,
                errors: [],
                bill : {
                    bill_type : {}
                },
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
                        if(this.bill.payment_date)
                            this.bill.payment_date = moment(this.bill.payment_date).format("YYYY-MM-DD")
                            
                        if(this.bill.source_document_date)
                            this.bill.source_document_date = moment(this.bill.source_document_date).format("YYYY-MM-DD")
                        
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
                    items.push({'label' : this.$t('items.show_bill'), disabled : true })
                }
                    
                return items
            },
            editBill() {
                this.$router.push({name: 'item_bill_edit'})
            },
            back() {
                this.$router.push({name: 'item_bills'})
            },
            payment() {
                this.$router.push({name: 'item_bill_payment'})
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
                <div class="flex justify-content-between align-items-center">
                    <Button type="button" :label="$t('app.back')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                </div>
                
                <div class="mt-5">
                    <Message severity="success" :closable="false" v-if="bill.paid">
                        {{ $t("items.bill_was_paid_on", [bill.paid_date]) }}
                    </Message>
                    
                    <Message severity="error" :closable="false" v-if="!bill.paid && bill.out_off_date">
                        {{ $t("items.payment_deadline_expired_on", [bill.payment_date]) }}
                    </Message>
                </div>
                
                <div class="grid mt-3">
                    <div class="col-12 md:col-8">
                        <table class="table">
                            <tr>
                                <td class="pl-0 font-medium" style="width: 280px">{{ $t('items.bill_type') }}:</td>
                                <td class="font-italic">{{ bill.bill_type.name }}</td>
                            </tr>
                            <tr>
                                <td class="pl-0 font-medium">{{ $t('items.cost') }}:</td>
                                <td class="font-italic">{{ numeralFormat(bill.cost, '0.00') }}</td>
                            </tr>
                            <tr>
                                <td class="pl-0 font-medium">{{ $t('items.payment_date') }}:</td>
                                <td class="font-italic">{{ bill.payment_date }}</td>
                            </tr>
                            <tr>
                                <td class="pl-0 font-medium">{{ $t('items.recipient_name') }}:</td>
                                <td class="font-italic">{{ bill.recipient_name }}</td>
                            </tr>
                            <tr>
                                <td class="pl-0 font-medium">{{ $t('items.recipient_desciption') }}:</td>
                                <td class="font-italic">{{ bill.recipient_desciption }}</td>
                            </tr>
                            <tr>
                                <td class="pl-0 font-medium">{{ $t('items.recipient_bank_account') }}:</td>
                                <td class="font-italic">{{ bill.recipient_bank_account }}</td>
                            </tr>
                            <tr>
                                <td class="pl-0 font-medium">{{ $t('items.source_document_number') }}:</td>
                                <td class="font-italic">{{ bill.source_document_number }}</td>
                            </tr>
                            <tr>
                                <td class="pl-0 font-medium">{{ $t('items.source_document_date') }}:</td>
                                <td class="font-italic">{{ bill.source_document_date }}</td>
                            </tr>
                            <tr>
                                <td class="pl-0 font-medium">{{ $t('items.comments') }}:</td>
                                <td class="font-italic">{{ bill.comments }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-12 md:col-4">
                        <Button :label="$t('items.update_bill')" @click="editBill" type="button" severity="primary" iconPos="right" icon="pi pi-pencil" class="w-full text-center" v-if="hasAccess('item:update')" :disabled="bill.paid" />
                        <Button :label="$t('items.add_payment')" @click="payment" type="button" severity="secondary" iconPos="right" icon="pi pi-dollar" class="w-full text-center mt-3" v-if="hasAccess('item:update')" :disabled="bill.paid" />
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</template>