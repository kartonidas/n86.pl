<script>
    import { appStore } from '@/store.js'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf} from '@/utils/i18n-validators'
    import { getResponseErrors, setMetaTitle, hasAccess, getValues } from '@/utils/helper'
    import moment from 'moment'
    
    import RentalService from '@/service/RentalService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.rent_payment_deposit')
            
            const rentalService = new RentalService()
            return {
                v: useVuelidate(),
                rentalService,
                hasAccess,
            }
        },
        data() {
            return {
                loading: true,
                saving: false,
                errors: [],
                rental: {},
                bills : {},
                payment: {
                    paid_date: new Date(),
                    cost: 0,
                },
                checked_bills: [],
                payment_methods: getValues('payments.methods'),
            }
        },
        beforeMount() {
            this.rentalService.get(this.$route.params.rentalId)
                .then(
                    (response) => {
                        this.rental = response.data
                        this.rentalService.bills(this.$route.params.rentalId, {size: 100, first: 0}, {"paid" : 0})
                            .then(
                                (response) => {
                                    this.loading = false
                                    this.bills = response.data;
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
                
                items.push({'label' : this.$t('rent.add_payment'), disabled : true })
                    
                return items
            },
            back() {
                this.$router.push({name: 'rental_show'})
            },
            getCheckboxInputId(id) {
                return "bill-" + id
            },
            calculateTotalCost() {
                let total = 0;
               
                if (this.checked_bills.length) {
                    this.bills.data.forEach((bill) => {
                        if (this.checked_bills.indexOf(bill.balance_document.id) !== -1) 
                            total += bill.cost;
                    });
                }
                this.payment.cost = total;
            },
            async submitForm() {
                const result = await this.v.$validate()
                if (result)
                {
                    let payment = Object.assign({}, this.payment);
                    if(payment.paid_date && payment.paid_date instanceof Date)
                        payment.paid_date = moment(payment.paid_date).format("YYYY-MM-DD")
                    
                    if(this.checked_bills)
                        payment.documents = this.checked_bills;
                    
                    this.rentalService.payment(this.$route.params.rentalId, payment)
                        .then(
                            (response) => {
                                this.saving = false
                                
                                this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('rent.rental_updated'), life: 3000 });
                                this.$router.push({name: 'rental_show'})
                            },
                            (errors) => {
                                this.saving = false
                                this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                                this.errors = getResponseErrors(errors)
                            }
                        )
                }
            }
        },
        validations () {
            return {
                payment: {
                    cost: { required },
                    paid_date: { required },
                    payment_method: { required },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid">
                <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
                    <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
                        <ul class="list-unstyled">
                            <li v-for="error of errors">
                                {{ error }}
                            </li>
                        </ul>
                    </Message>
                    
                    <h3 class="mt-2 mb-4 text-color">{{ $t('rent.add_payment_short') }}</h3>
                        
                    <div v-if="bills.data">
                        <h5 class="mt-2 mb-1 text-color">{{ $t('rent.bills') }}</h5>
                        <ul class="list-unstyled">
                            <li v-for="(bill, index) in bills.data" class="pb-2">
                                <Checkbox v-model="checked_bills" name="bills" :inputId="getCheckboxInputId(bill.id)" :value="bill.balance_document.id" @change="calculateTotalCost"/>
                                <label :for="getCheckboxInputId(bill.id)" class="pl-2">
                                    {{ bill.bill_type.name }}: <span class="font-medium">{{ numeralFormat(bill.cost, '0.00') }} {{ bill.currency }}</span>
                                    ({{ bill.payment_date }})
                                </label>
                            </li>    
                        </ul>
                    </div>
                    
                    <div class="formgrid grid">
                        <div class="field col-12 md:col-4 mb-4">
                            <label for="cost" v-required class="block text-900 font-medium mb-2">{{ $t('items.cost') }}</label>
                            <InputNumber id="cost" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.cost')" class="w-full" :class="{'p-invalid' : v.payment.cost.$error}" v-model="payment.cost" :disabled="loading || saving"/>
                            <div v-if="v.payment.cost.$error">
                                <small class="p-error">{{ v.payment.cost.$errors[0].$message }}</small>
                            </div>
                        </div>
                        
                        <div class="field col-12 md:col-4 mb-4">
                            <label for="cost" v-required class="block text-900 font-medium mb-2">{{ $t('items.paid_date') }}</label>
                            <Calendar id="paid_date" v-model="payment.paid_date" :class="{'p-invalid' : v.payment.paid_date.$error}" :placeholder="$t('items.paid_date')" showIcon :disabled="loading || saving"/>
                            <div v-if="v.payment.paid_date.$error">
                                <small class="p-error">{{ v.payment.paid_date.$errors[0].$message }}</small>
                            </div>
                        </div>
                        
                        <div class="field col-12 md:col-4 mb-4">
                            <label for="cost" v-required class="block text-900 font-medium mb-2">{{ $t('items.payment_method') }}</label>
                            <Dropdown id="payment_method" v-model="payment.payment_method" :options="payment_methods" optionLabel="name" optionValue="id" :class="{'p-invalid' : v.payment.payment_method.$error}" :placeholder="$t('items.select_payment_method')" class="w-full" :disabled="loading || saving"/>
                            <div v-if="v.payment.payment_method.$error">
                                <small class="p-error">{{ v.payment.payment_method.$errors[0].$message }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <div v-if="loading">
                            <ProgressSpinner style="width: 25px; height: 25px"/>
                        </div>
                        
                        <div class="flex justify-content-between align-items-center">
                            <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                            <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</template>