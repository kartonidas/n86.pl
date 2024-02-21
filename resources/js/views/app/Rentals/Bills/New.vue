<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import BillForm from './../../Items/Bills/_Form.vue'
    import RentalService from '@/service/RentalService'
    
    export default {
        components: { BillForm },
        setup() {
            setMetaTitle('meta.title.items_new_bill')
            
            const rentalService = new RentalService()
            return {
                rentalService,
            }
        },
        data() {
            return {
                rental: {},
                errors: [],
                bill : {
                    charge_current_tenant: 1
                },
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
                
                items.push({'label' : this.$t('items.new_bill'), disabled : true })
                    
                return items
            },
            
            async createBill(bill) {
                this.saving = true
                this.errors = []
                
                this.rentalService.createBill(this.$route.params.rentalId, bill)
                    .then(
                        (response) => {
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('items.bill_added'),
                            });
                            
                            this.$router.push({name: 'rental_bill_show', params: { billId : response.data }})
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            },
            
            back() {
                this.$goBack('rental_show');
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
                <BillForm @submit-form="createBill" @back="back" :bill="bill" :saving="saving" :errors="errors" />
            </div>
        </div>
    </div>
</template>