<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import BillForm from './../../Items/Bills/_Form.vue'
    import RentalService from '@/service/RentalService'
    import moment from 'moment'
    
    export default {
        components: { BillForm },
        setup() {
            setMetaTitle('meta.title.items_update_bill')
            
            const rentalService = new RentalService()
            return {
                rentalService,
            }
        },
        data() {
            return {
                rental: {},
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
                
                items.push({'label' : this.$t('items.update_bill'), disabled : true })
                    
                return items
            },
            
            async updateBill(bill) {
                this.saving = true
                this.errors = []
                
                this.rentalService.updateBill(this.$route.params.rentalId, this.$route.params.billId, bill)
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
                this.$goBack('rental_bill_show');
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <BillForm @submit-form="updateBill" @back="back" :bill="bill" source="update" :saving="saving" :loading="loading" :errors="errors" />
            </div>
        </div>
    </div>
</template>