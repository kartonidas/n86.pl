<script>
    import { appStore } from '@/store.js'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf} from '@/utils/i18n-validators'
    import { getResponseErrors, getValues, setMetaTitle } from '@/utils/helper'
    import moment from 'moment'
    
    import RentalService from '@/service/RentalService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.items_show_bill')
            
            const rentalService = new RentalService()
            return {
                v: useVuelidate(),
                rentalService,
            }
        },
        data() {
            return {
                loading: true,
                saving: false,
                selectTypeDisabled: false,
                errors: [],
                rental: {},
                bill : {
                    bill_type : {}
                },
                payment_methods: getValues('payments.methods'),
                payment : {
                    type: "deposit",
                    paid_date: new Date(),
                    payment_method: "cash"
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
                                    if (this.bill.rental_id == 0)
                                    {
                                        this.payment.type = "setpaid";
                                        this.selectTypeDisabled = true;
                                    }
                                    this.payment.cost = this.bill.cost
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
            async submitForm() {
                const result = await this.v.$validate()
                if (result)
                {
                    let payment = Object.assign({}, this.payment);
                    if(payment.paid_date && payment.paid_date instanceof Date)
                        payment.paid_date = moment(payment.paid_date).format("YYYY-MM-DD")
                    
                    this.rentalService.paymentBill(this.$route.params.rentalId, this.$route.params.billId, payment)
                        .then(
                            (response) => {
                                appStore().setToastMessage({
                                    severity : 'success',
                                    summary : this.$t('app.success'),
                                    detail : this.$t('items.bill_paid'),
                                });
                                
                                this.$router.push({name: 'rental_bill_show'})
                            },
                            (response) => {
                                this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                                this.errors = getResponseErrors(response);
                                this.saving = false;
                            },
                        );
                    
                }
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            back() {
                this.$goBack('rental_bill_show');
            },
        },
        validations () {
            return {
                payment: {
                    cost: { required: requiredIf(function() { return this.payment.type == "deposit" }) },
                    paid_date: { required: requiredIf(function() { return this.payment.type == "deposit" }) },
                    payment_method: { required: requiredIf(function() { return this.payment.type == "deposit" }) },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card pt-4">
                <Help show="item:payment|rental" mark="item:payment" class="text-right mb-3"/>
                <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
                    <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
                        <ul class="list-unstyled">
                            <li v-for="error of errors">
                                {{ error }}
                            </li>
                        </ul>
                    </Message>
                    <div class="mb-4">
                        <div class="p-fluid">
                            <div class="formgrid grid">
                                <div class="field col-12">
                                    <div class="flex align-items-center mb-2">
                                        <RadioButton v-model="payment.type" inputId="radio1" value="deposit" :disabled="loading || saving || selectTypeDisabled" />
                                        <label for="radio1" class="ml-2">{{ $t('items.post_customer_payment') }}</label>
                                    </div>
                                    <div class="flex align-items-center">
                                        <RadioButton v-model="payment.type" inputId="radio2" value="setpaid" :disabled="loading || saving || selectTypeDisabled"/>
                                        <label for="radio2" class="ml-2">{{ $t('items.set_paid') }}</label>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-4 mb-4">
                                    <label for="cost" v-required class="block text-900 font-medium mb-2">{{ $t('items.cost') }}</label>
                                    <InputNumber id="cost" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.cost')" class="w-full" :class="{'p-invalid' : v.payment.cost.$error}" v-model="payment.cost" :disabled="true"/>
                                    <div v-if="v.payment.cost.$error">
                                        <small class="p-error">{{ v.payment.cost.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-4 mb-4">
                                    <label for="cost" v-required class="block text-900 font-medium mb-2">{{ $t('items.paid_date') }}</label>
                                    <Calendar id="paid_date" v-model="payment.paid_date" :class="{'p-invalid' : v.payment.paid_date.$error}" :placeholder="$t('items.paid_date')" showIcon :disabled="loading || saving || payment.type != 'deposit'"/>
                                    <div v-if="v.payment.paid_date.$error">
                                        <small class="p-error">{{ v.payment.paid_date.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-4 mb-4">
                                    <label for="cost" v-required class="block text-900 font-medium mb-2">{{ $t('items.payment_method') }}</label>
                                    <Dropdown id="payment_method" v-model="payment.payment_method" :options="payment_methods" optionLabel="name" optionValue="id" :class="{'p-invalid' : v.payment.payment_method.$error}" :placeholder="$t('items.select_payment_method')" class="w-full" :disabled="loading || saving || payment.type != 'deposit'"/>
                                    <div v-if="v.payment.payment_method.$error">
                                        <small class="p-error">{{ v.payment.payment_method.$errors[0].$message }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-footer">
                        <div v-if="loading">
                            <ProgressSpinner style="width: 25px; height: 25px"/>
                        </div>
                        
                        <div class="flex justify-content-between align-items-center">
                            <Button type="button" :label="$t('app.cancel')" v-if="!loading" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                            <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>