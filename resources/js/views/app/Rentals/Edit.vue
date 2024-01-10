<script>
    import { ref, reactive, computed } from 'vue'
    import { getValues, getResponseErrors, setMetaTitle, p } from '@/utils/helper'
    import { appStore } from '@/store.js'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf, minValue, maxLength, maxValue } from '@/utils/i18n-validators'
    import moment from 'moment'
    
    import RentalService from '@/service/RentalService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.rent_edit')
            
            const rentalService = new RentalService()
            
            return {
                rentalService,
            }
        },
        watch: {
            rent() {
                this.toValidate = this.rent
            }
        },
        data() {
            const period = getValues('rental.periods');
            const termination_period = getValues('rental.termination_periods');
            const payment = getValues('rental.payments');
            const payment_days = getValues('rental.payment_days');
            
            const toValidate = ref(this.rent)
            const state = reactive({ 'rent' : toValidate })
            const rules = computed(() => {
                const rules = {
                    rent: {
                        start_date: { required },
                        document_date: { required },
                        period: { required },
                        end_date: { required: requiredIf(function() { return this.rent.period == "date" }) },
                        termination_period: { required },
                        payment: { required },
                        rent: { required, minValue: minValue(1), maxValueValue: maxValue(999999.99) },
                        deposit: { maxValueValue: maxValue(999999.99) },
                        first_payment_date: { required: requiredIf(function() { return this.rent.payment == "cyclical" }) },
                        payment_day: { required: requiredIf(function() { return this.rent.payment == "cyclical" }) },
                        number_of_people: { required, maxValueValue: maxValue(99) },
                        comments: { maxLengthValue: maxLength(5000) },
                    }
                }
                
                if(state.rent != undefined) {
                    rules.rent.months = state.rent.period == "month" ? { required, maxValueValue: maxValue(120) } : {}
                    rules.rent.termination_months = state.rent.termination_period == "months" ? { required, maxValueValue: maxValue(24) } : {}
                    rules.rent.termination_days = state.rent.termination_period == "days" ? { required, maxValueValue: maxValue(99) } : {}
                    rules.rent.first_month_different_amount_value = state.rent.first_month_different_amount ? { required, maxValueValue: maxValue(99999.99) } : {}
                    rules.rent.last_month_different_amount_value = state.rent.last_month_different_amount ? { required, maxValueValue: maxValue(99999.99) } : {}
                } else {
                    rules.rent.months = {}
                    rules.rent.termination_months = {}
                    rules.rent.termination_days = {}
                    rules.rent.first_month_different_amount_value = {}
                    rules.rent.last_month_different_amount_value = {}
                }
                
                return rules
            });
            
            return {
                rent : {},
                saving: false,
                loading: true,
                errors: [],
                period,
                termination_period,
                payment,
                payment_days,
                v: useVuelidate(rules, state),
                toValidate,
                v: useVuelidate(rules, state),
            }
        },
        beforeMount() {
            if(appStore().toastMessage) {
                let m = appStore().toastMessage
                this.$toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                appStore().setToastMessage(null)
            }
            
            this.rentalService.get(this.$route.params.rentalId)
                .then(
                    (response) => {
                        this.rent = response.data
                        
                         if(this.rent.start && !(this.rent.start instanceof Date))
                            this.rent.start_date = moment(this.rent.start).toDate()
                            
                        if(this.rent.end && !(this.rent.end instanceof Date))
                            this.rent.end_date = moment(this.rent.end).toDate()
                            
                        if(this.rent.first_payment_date && !(this.rent.first_payment_date instanceof Date))
                            this.rent.first_payment_date = moment(this.rent.first_payment_date).toDate()
                        
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
                    {'label' : this.$t('menu.rentals_list'), route : { name : 'rentals'}  },
                ]
                
                if(this.rent.full_number != undefined)
                    items.push({'label' : this.rent.full_number, route : { name : 'rental_show'} })
                
                items.push({'label' : this.$t('rent.update_rental'), disabled : true })
                    
                return items
            },
            
            async submitForm() {
                const result = await this.v.$validate()
                if (result) {
                    this.saving = true
                    
                    let rent = Object.assign({}, this.rent);
                    if(rent.start_date && rent.start_date instanceof Date)
                        rent.start_date = moment(rent.start_date).format("YYYY-MM-DD")
                    
                    if(rent.end_date && rent.end_date instanceof Date)
                        rent.end_date = moment(rent.end_date).format("YYYY-MM-DD")
                        
                    if(rent.document_date && rent.document_date instanceof Date)
                        rent.document_date = moment(rent.document_date).format("YYYY-MM-DD")
                        
                    if(rent.first_payment_date && rent.first_payment_date instanceof Date)
                        rent.first_payment_date = moment(rent.first_payment_date).format("YYYY-MM-DD")
                        
                    this.rentalService.update(this.$route.params.rentalId, rent)
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
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$router.push({name: 'rental_show'})
            },
            
            onChangeStartDate(date) {
                this.rent.first_payment_date = moment(date).add(10, 'days').toDate()
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    <div class="grid mt-1">
        <div class="col col-12">
            <div class="card">
                <h3 class="mt-2 mb-6 text-color">{{ $t('rent.rental_agreement_no', [rent.full_number]) }}</h3>
                
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
                                <div class="field col-12 md:col-6 xl:col-3 mb-4">
                                    <label for="document_date" v-required class="block text-900 font-medium mb-2">{{ $t('rent.document_date') }}</label>
                                    <Calendar id="document_date" v-model="rent.document_date" :class="{'p-invalid' : v.rent.document_date.$error}" :placeholder="$t('rent.document_date')" showIcon :disabled="loading || saving"/>
                                    <div v-if="v.rent.document_date.$error">
                                        <small class="p-error">{{ v.rent.document_date.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 xl:col-3 mb-4">
                                    <label for="start_date" v-required class="block text-900 font-medium mb-2">{{ $t('rent.start_date') }}</label>
                                    <Calendar id="start_date" v-model="rent.start_date" @date-select="onChangeStartDate" :class="{'p-invalid' : v.rent.start_date.$error}" :placeholder="$t('rent.start_date')" showIcon :disabled="loading || saving"/>
                                    <div v-if="v.rent.start_date.$error">
                                        <small class="p-error">{{ v.rent.start_date.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 xl:col-3 mb-4" :class="[rent.period == 'indeterminate' ? 'md:col-8' : 'mmd:col-4']">
                                    <label for="period" v-required class="block text-900 font-medium mb-2">{{ $t('rent.period') }}</label>
                                    <Dropdown id="period" v-model="rent.period" :options="period" optionLabel="name" :class="{'p-invalid' : v.rent.period.$error}" optionValue="id" :placeholder="$t('rent.select_period')" class="w-full" :disabled="loading || saving"/>
                                    <div v-if="v.rent.period.$error">
                                        <small class="p-error">{{ v.rent.period.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 xl:col-3 mb-4" v-if="rent.period == 'month'">
                                    <label for="months" v-required class="block text-900 font-medium mb-2">{{ $t('rent.number_of_months') }}</label>
                                    <InputMask mask="9?99" slotChar="" :class="{'p-invalid' : v.rent.months.$error}" :placeholder="$t('rent.number_of_months')" class="w-full" v-model="rent.months" :disabled="saving || loading" />
                                    <div v-if="v.rent.months.$error">
                                        <small class="p-error">{{ v.rent.months.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-4 mb-4" v-if="rent.period == 'date'">
                                    <label for="end_date" v-required class="block text-900 font-medium mb-2">{{ $t('rent.end_date') }}</label>
                                    <Calendar id="end_date" v-model="rent.end_date" :class="{'p-invalid' : v.rent.end_date.$error}" :placeholder="$t('rent.end_date')" :minDate="rent.start_date" showIcon :disabled="loading || saving"/>
                                    <div v-if="v.rent.end_date.$error">
                                        <small class="p-error">{{ v.rent.end_date.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="termination_period" v-required class="block text-900 font-medium mb-2">{{ $t('rent.termination_period') }}</label>
                                    <Dropdown id="termination_period" v-model="rent.termination_period" :options="termination_period" optionLabel="name" :class="{'p-invalid' : v.rent.termination_period.$error}" optionValue="id" :placeholder="$t('rent.select_termination_period')" class="w-full" :disabled="loading || saving"/>
                                    <div v-if="v.rent.termination_period.$error">
                                        <small class="p-error">{{ v.rent.termination_period.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4" v-if="rent.termination_period == 'months'">
                                    <label for="termination_months" v-required class="block text-900 font-medium mb-2">{{ $t('rent.termination_months') }}</label>
                                    <InputMask mask="9?99" slotChar="" :class="{'p-invalid' : v.rent.termination_months.$error}" :placeholder="$t('rent.termination_months')" class="w-full" v-model="rent.termination_months" :disabled="saving || loading"/>
                                    <div v-if="v.rent.termination_months.$error">
                                        <small class="p-error">{{ v.rent.termination_months.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4" v-if="rent.termination_period == 'days'">
                                    <label for="termination_days" v-required class="block text-900 font-medium mb-2">{{ $t('rent.termination_days') }}</label>
                                    <InputMask mask="9?9" slotChar="" :class="{'p-invalid' : v.rent.termination_days.$error}" :placeholder="$t('rent.termination_days')" class="w-full" v-model="rent.termination_days" :disabled="saving || loading"/>
                                    <div v-if="v.rent.termination_days.$error">
                                        <small class="p-error">{{ v.rent.termination_days.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <Divider align="left" class="mt-3 mb-5">
                                    {{ $t('rent.payments') }}
                                </Divider>
                                
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="deposit" class="block text-900 font-medium mb-2">{{ $t('rent.deposit') }}</label>
                                    <InputNumber id="deposit" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('rent.deposit')" class="w-full" :class="{'p-invalid' : v.rent.deposit.$error}" v-model="rent.deposit" :disabled="loading || saving || rent.has_paid_deposit"/>
                                    <div v-if="v.rent.deposit.$error">
                                        <small class="p-error">{{ v.rent.deposit.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="payment" v-required class="block text-900 font-medium mb-2">{{ $t('rent.payment') }}</label>
                                    <Dropdown id="payment" v-model="rent.payment" :options="payment" optionLabel="name" :class="{'p-invalid' : v.rent.payment.$error}" optionValue="id" :placeholder="$t('rent.select_payment')" class="w-full" :disabled="true"/>
                                    <div v-if="v.rent.payment.$error">
                                        <small class="p-error">{{ v.rent.payment.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 mb-4" :class="[rent.payment == 'cyclical' ? 'md:col-4' : 'md:col-6']">
                                    <label for="rent" v-required class="block text-900 font-medium mb-2">{{ $t('rent.rent') }}</label>
                                    <InputNumber id="rent" :useGrouping="false" locale="pl-PL" :class="{'p-invalid' : v.rent.rent.$error}" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('rent.rent')" class="w-full" v-model="rent.rent" :disabled="loading || saving"/>
                                    <div v-if="v.rent.rent.$error">
                                        <small class="p-error">{{ v.rent.rent.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-4 mb-4" v-if="rent.payment == 'cyclical'">
                                    <label for="firstMonthDifferentAmount" class="block text-900 font-medium mb-2">{{ $t('rent.first_month_different_amount') }}</label>
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="firstMonthDifferentAmount" name="superuser" value="1" v-model="rent.first_month_different_amount" class="mr-2" :binary="true" :disabled="loading || saving || rent.has_generated_rent"/>
                                        <InputNumber :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" class="w-full" :class="{'p-invalid' : v.rent.first_month_different_amount_value.$error}" v-model="rent.first_month_different_amount_value" :disabled="loading || saving || !rent.first_month_different_amount || rent.has_generated_rent"/>
                                    </div>
                                    <div v-if="v.rent.first_month_different_amount_value.$error">
                                        <small class="p-error">{{ v.rent.first_month_different_amount_value.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-4 mb-4" v-if="rent.payment == 'cyclical'">
                                    <label for="lastMonthDifferentAmount" class="block text-900 font-medium mb-2">{{ $t('rent.last_month_different_amount') }}</label>
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="lastMonthDifferentAmount" name="superuser" value="1" v-model="rent.last_month_different_amount" class="mr-2" :binary="true" :disabled="loading || saving || rent.has_generated_rent"/>
                                        <InputNumber :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" class="w-full" :class="{'p-invalid' : v.rent.last_month_different_amount_value.$error}" v-model="rent.last_month_different_amount_value" :disabled="loading || saving || !rent.last_month_different_amount || rent.has_generated_rent"/>
                                    </div>
                                    <div v-if="v.rent.last_month_different_amount_value.$error">
                                        <small class="p-error">{{ v.rent.last_month_different_amount_value.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4" v-if="rent.payment == 'cyclical'">
                                    <label for="payment_day" v-required class="block text-900 font-medium mb-2">{{ $t('rent.payment_day') }}</label>
                                    <Dropdown id="payment_day" v-model="rent.payment_day" :options="payment_days" optionLabel="name" :class="{'p-invalid' : v.rent.payment_day.$error}" optionValue="id" :placeholder="$t('rent.select_payment_day')" class="w-full" :disabled="loading || saving"/>
                                    <div v-if="v.rent.payment_day.$error">
                                        <small class="p-error">{{ v.rent.payment_day.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 md:col-6 mb-4">
                                    <label for="first_payment_date" v-required class="block text-900 font-medium mb-2">{{ $t('rent.first_payment_date') }}</label>
                                    <Calendar id="first_payment_date" v-model="rent.first_payment_date" :class="{'p-invalid' : v.rent.first_payment_date.$error}" :placeholder="$t('rent.first_payment_date')" :minDate="rent.start_date" showIcon :disabled="loading || saving"/>
                                    <div v-if="v.rent.first_payment_date.$error">
                                        <small class="p-error">{{ v.rent.first_payment_date.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <Divider align="left" class="mt-3 mb-5">
                                    {{ $t('rent.additional_data') }}
                                </Divider>
                                
                                <div class="field col-12 mb-4">
                                    <label for="number_of_people" v-required class="block text-900 font-medium mb-2">{{ $t('rent.number_of_people') }}</label>
                                    <InputMask id="number_of_people" mask="9?9" slotChar="" :placeholder="$t('rent.number_of_people')" class="w-full" :class="{'p-invalid' : v.rent.number_of_people.$error}" v-model="rent.number_of_people" :disabled="saving || loading"/>
                                    <div v-if="v.rent.number_of_people.$error">
                                        <small class="p-error">{{ v.rent.number_of_people.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 mb-4">
                                    <label for="comments" class="block text-900 font-medium mb-2">{{ $t('rent.comments') }}</label>
                                    <Textarea id="comments" type="text" :placeholder="$t('rent.comments')" rows="3" class="w-full" :class="{'p-invalid' : v.rent.comments.$error}" v-model="rent.comments" :disabled="loading || saving"/>
                                    <div v-if="v.rent.comments.$error">
                                        <small class="p-error">{{ v.rent.comments.$errors[0].$message }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <div class="flex justify-content-between align-items-center">
                            <Button type="button" :label="$t('app.back')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                            <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>