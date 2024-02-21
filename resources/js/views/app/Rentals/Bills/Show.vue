<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle, hasAccess } from '@/utils/helper'
    import moment from 'moment'
    
    import RentalService from '@/service/RentalService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.items_show_bill')
            
            const rentalService = new RentalService()
            return {
                rentalService,
                hasAccess,
            }
        },
        data() {
            return {
                loading: true,
                errors: [],
                rental: {},
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
            
            this.rentalService.get(this.$route.params.rentalId)
                .then(
                    (response) => {
                        this.rental = response.data
                        this.rentalService.getBill(this.$route.params.rentalId, this.$route.params.billId)
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
                    (errors) => {
                        if(errors.response.status == 404)
                        {
                            appStore().setError404(errors.response.data.message);
                            this.$router.push({name: 'objectnotfound'})
                        }
                        else
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                    }
                )
        },
        methods: {
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.rentals_list'), route : { name : 'rentals'}  },
                ]
                
                if(this.rental.full_number != undefined)
                    items.push({'label' : this.rental.full_number, route : { name : 'rental_show'} })
                
                items.push({'label' : this.$t('items.show_bill'), disabled : true })
                    
                return items
            },
            editBill() {
                this.$router.push({name: 'rental_bill_edit'})
            },
            back() {
                this.$goBack('rental_show');
            },
            payment() {
                this.$router.push({name: 'rental_bill_payment'})
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card pt-4">
                <Help show="rental" class="text-right mb-3"/>
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
                        <div class="grid">
                            <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                <span class="font-medium">{{ $t('items.bill_type') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                {{ bill.bill_type.name }}
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                <span class="font-medium">{{ $t('items.cost') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                {{ numeralFormat(bill.cost, '0.00') }} {{ bill.currency }}
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                <span class="font-medium">{{ $t('items.payment_date') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                {{ bill.payment_date }}
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                <span class="font-medium">{{ $t('items.payer') }}:</span>
                            </div>
                            <div class="col-12 sm:col-7 pt-0 pb-1">
                                <span v-if="bill.rental_id > 0">{{ $t('items.currently_tenant') }}</span>
                                <span v-else>{{ $t('items.owner') }}</span>
                            </div>
                            <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            
                            <template v-if="bill.recipient_name">
                                <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                    <span class="font-medium">{{ $t('items.payer') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ bill.recipient_name }}
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            </template>
                            
                            <template v-if="bill.recipient_desciption">
                                <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                    <span class="font-medium">{{ $t('items.recipient_desciption') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ bill.recipient_desciption ?? '-' }}
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            </template>
                            
                            <template v-if="bill.recipient_bank_account">
                                <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                    <span class="font-medium">{{ $t('items.recipient_bank_account') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ bill.recipient_bank_account ?? '-' }}
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            </template>
                            
                            <template v-if="bill.source_document_number">
                                <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                    <span class="font-medium">{{ $t('items.source_document_number') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ bill.source_document_number ?? '-' }}
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            </template>
                            
                            <template v-if="bill.source_document_date">
                                <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                    <span class="font-medium">{{ $t('items.source_document_date') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ bill.source_document_date ?? '-' }}
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            </template>
                            
                            <template v-if="bill.comments">
                                <div class="col-fixed pt-0 pb-1" style="width: 230px">
                                    <span class="font-medium">{{ $t('items.comments') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ bill.comments ?? '-' }}
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            </template>
                        </div>
                    </div>
                    <div class="col-12 md:col-4">
                        <Button :label="$t('items.update_bill')" @click="editBill" type="button" severity="primary" iconPos="right" icon="pi pi-pencil" class="w-full text-center" v-if="hasAccess('rent:update')" :disabled="bill.paid" />
                        <Button :label="$t('items.add_payment')" @click="payment" type="button" severity="secondary" iconPos="right" icon="pi pi-dollar" class="w-full text-center mt-3" v-if="hasAccess('rent:update')" :disabled="bill.paid" />
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</template>