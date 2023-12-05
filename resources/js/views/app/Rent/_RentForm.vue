<script>
    import { ref } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf } from '@/utils/i18n-validators'
    import moment from 'moment'

    export default {
        emits: ['submit-form', 'back'],
        data() {
            const rent = ref({
                start_date: new Date(),
                period: 'month',
                months: 12,
                termination_period: 'months',
                termination_days : 30,
                termination_months : 3,
                deposit: this.item.default_deposit,
                payment: 'cyclical',
                rent: this.item.default_rent,
            })
            
            const period = [
                {"id" : "month", "name" : "W miesiącach"},
                {"id" : "indeterminate", "name" : "Nieokreślony"},
                {"id" : "date", "name" : "Konkretna data"},
            ];
            
            const termination_period = [
                {"id" : "months", "name" : "Liczony w miesiącach"},
                {"id" : "days", "name" : "Liczony w dniach"},
            ];
            
            const payment = [
                {"id" : "cyclical", "name" : "Cykliczna"},
                {"id" : "onetime", "name" : "Jednorazowa"},
            ];
            
            return {
                period,
                termination_period,
                payment,
                rent,
                v: useVuelidate(),
            }
        },
        props: {
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            item: { type: Object },
        },
        methods: {
            back() {
                this.$emit('back')
            },
            
            async submitForm() {
                const result = await this.v.$validate()
                if (result) {
                    console.log(this.rent)
                }
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            }
        },
        validations () {
            return {
                rent: {
                    start_date: { required },
                    end_date: { required },
                    period: { required },
                    months: { required },
                    termination_period: { required },
                    termination_months: { required },
                    termination_days: { required },
                    payment: { required },
                    rent: { required },
                    first_payment_date: { required },
                }
            }
        },
    }
</script>

<template>
    <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
        <div class="mb-4">
            <div class="p-fluid">
                <div class="formgrid grid">
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="start_date" v-required class="block text-900 font-medium mb-2">{{ $t('rent.start_date') }}</label>
                        <Calendar id="start_date" v-model="rent.start_date" :class="{'p-invalid' : v.rent.start_date.$error}" :placeholder="$t('rent.start_date')" dateFormat="yy-mm-dd" showIcon :disabled="loading || saving"/>
                        <div v-if="v.rent.start_date.$error">
                            <small class="p-error">{{ v.rent.start_date.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4" :class="[rent.period == 'indeterminate' ? 'md:col-8' : 'mmd:col-4']">
                        <label for="period" v-required class="block text-900 font-medium mb-2">{{ $t('rent.period') }}</label>
                        <Dropdown id="period" v-model="rent.period" :options="period" optionLabel="name" :class="{'p-invalid' : v.rent.period.$error}" optionValue="id" :placeholder="$t('rent.select_period')" class="w-full" :disabled="loading || saving"/>
                        <div v-if="v.rent.period.$error">
                            <small class="p-error">{{ v.rent.period.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4" v-if="rent.period == 'month'">
                        <label for="months" v-required class="block text-900 font-medium mb-2">{{ $t('rent.months') }}</label>
                        <InputMask mask="9?99" slotChar="" :class="{'p-invalid' : v.rent.months.$error}" :placeholder="$t('rent.months')" class="w-full" v-model="rent.months" :disabled="saving || loading"/>
                        <div v-if="v.rent.months.$error">
                            <small class="p-error">{{ v.rent.months.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4" v-if="rent.period == 'date'">
                        <label for="end_date" v-required class="block text-900 font-medium mb-2">{{ $t('rent.end_date') }}</label>
                        <Calendar id="end_date" v-model="rent.end_date" :class="{'p-invalid' : v.rent.end_date.$error}" :placeholder="$t('rent.end_date')" :minDate="rent.start_date" dateFormat="yy-mm-dd" showIcon :disabled="loading || saving"/>
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
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="deposit" class="block text-900 font-medium mb-2">{{ $t('rent.deposit') }}</label>
                        <InputNumber id="deposit" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('rent.deposit')" class="w-full" v-model="rent.deposit" :disabled="loading || saving"/>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="payment" v-required class="block text-900 font-medium mb-2">{{ $t('rent.payment') }}</label>
                        <Dropdown id="payment" v-model="rent.payment" :options="payment" optionLabel="name" :class="{'p-invalid' : v.rent.payment.$error}" optionValue="id" :placeholder="$t('rent.select_payment')" class="w-full" :disabled="loading || saving"/>
                        <div v-if="v.rent.payment.$error">
                            <small class="p-error">{{ v.rent.payment.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="rent" v-required class="block text-900 font-medium mb-2">{{ $t('rent.rent') }}</label>
                        <InputNumber id="rent" :useGrouping="false" locale="pl-PL" :class="{'p-invalid' : v.rent.rent.$error}" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('rent.rent')" class="w-full" v-model="rent.rent" :disabled="loading || saving"/>
                        <div v-if="v.rent.rent.$error">
                            <small class="p-error">{{ v.rent.rent.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-4 mb-4">
                        <label for="first_payment_date" v-required class="block text-900 font-medium mb-2">{{ $t('rent.first_payment_date') }}</label>
                        <Calendar id="first_payment_date" v-model="rent.first_payment_date" :class="{'p-invalid' : v.rent.first_payment_date.$error}" :placeholder="$t('rent.first_payment_date')" :minDate="rent.start_date" dateFormat="yy-mm-dd" showIcon :disabled="loading || saving"/>
                        <div v-if="v.rent.first_payment_date.$error">
                            <small class="p-error">{{ v.rent.first_payment_date.$errors[0].$message }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <ul>
            <li>Do którego dnia miesiąca płatność (to można by było ustawiać domyślnie w konfiguracji)</li>
            <li>Data pierwszej płatności (domyślnie np liczyć + 14 dni od daty rozpoczęcia)</li>
            <li>Inna kwota pierwszego miesiąca (jeśli np zaczynamy w połowie i jest to płatność cykliczna)</li>
            <li>Inna kwota ostatniego miesiąca (jeśli np kończymy w połowie i jest to płatność cykliczna)</li>
            <li>Ilość osób</li>
            <li>Uwagi</li>
            <li>Numer dowodu / paszportu najemcy?</li>
        </ul>
        
        <div class="form-footer">
            <div class="flex justify-content-between align-items-center">
                <Button type="button" :label="$t('app.back')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                <Button type="submit" :label="$t('app.next')" :loading="saving" iconPos="right" icon="pi pi-angle-right" class="w-auto text-center"></Button>
            </div>
        </div>
    </form>
</template>