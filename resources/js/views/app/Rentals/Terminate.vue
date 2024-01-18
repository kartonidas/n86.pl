<script>
    import { getValueLabel, getResponseErrors, hasAccess, setMetaTitle, p } from '@/utils/helper'
    import { useVuelidate } from '@vuelidate/core'
    import { required,  requiredIf, maxLength } from '@/utils/i18n-validators'
    import { appStore } from '@/store.js'
    import moment from 'moment'
    
    import Address from '@/views/app/_partials/Address.vue'
    import Header from '@/views/app/_partials/Header.vue'
    import Rental from '@/views/app/_partials/Rental.vue'
    import RentalService from '@/service/RentalService'
    
    export default {
        components: { Address, Header, Rental },
        setup() {
            setMetaTitle('meta.title.rent_show')
            
            const rentalService = new RentalService()
            
            return {
                p,
                v: useVuelidate(),
                rentalService,
                hasAccess,
                getValueLabel,
            }
        },
        data() {
            return {
                displayConfirmation: false,
                block: false,
                errors: [],
                rental: {
                    item: {},
                    tenant: {},
                },
                terminate: {
                    mode: "date"
                },
                terminate_modal: {},
                loading: true,
                saving: false,
                terminate_mode: [
                    {"id" : "date", "name" : this.$t('rent.terminate_mode_date')},
                    {"id" : "immediately", "name" : this.$t('rent.terminate_mode_immediately')},
                ]
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
                        this.rental = response.data
                        
                        if ((this.rental.termination && this.rental.status == 'current') || this.rental.status != 'current')
                            this.block = true
                        
                        switch(this.rental.termination_period) {
                            case "months":
                                this.terminate.end_date = moment().add(this.rental.termination_months, "M").toDate()
                            break;
                            
                            case "days":
                                this.terminate.end_date = moment().add(this.rental.termination_days, "d").toDate()
                            break;
                        }
                        
                        if (this.rental.termination) {
                            this.terminate.end_date = this.rental.termination_time
                            this.terminate.termination_reason = this.rental.termination_reason
                        }
                        
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
            async submitForm() {
                let fee = Object.assign({}, this.fee);
                    
                const result = await this.v.$validate()
                if (result)
                {
                    let terminate = Object.assign({}, this.terminate);
                    if(terminate.end_date && terminate.end_date instanceof Date)
                        terminate.end_date = moment(terminate.end_date).format("YYYY-MM-DD")
                    
                    if(terminate.mode == "immediately")
                        terminate.end_date = moment().format("YYYY-MM-DD")
                    
                    if(this.displayConfirmation)
                    {
                        this.saving = true
                        this.rentalService.terminate(this.$route.params.rentalId, terminate)
                            .then(
                                (response) => {
                                    this.saving = false
                                    
                                    appStore().setToastMessage({
                                        severity : 'success',
                                        summary : this.$t('app.success'),
                                        detail : this.$t('rent.contract_has_been_terminated'),
                                    });
                                    
                                    this.$router.push({name: 'rental_show'})
                                },
                                (errors) => {
                                    this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                                    this.saving = false
                                }
                            )
                    }
                    else
                    {
                        this.displayConfirmation = true
                        this.terminate_modal.end_date = terminate.end_date
                    }
                }
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$router.push({name: 'rental_show'})
            },
            
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.rentals_list'), route : { name : 'rentals'}  },
                ]
                
                if(this.rental.full_number != undefined)
                {
                    items.push({'label' : this.rental.full_number, route : { name : 'rental_show'} })
                    items.push({'label' : this.$t('rent.terminate_contract'), disabled : true })
                }
                    
                return items
            },
        },
        validations () {
            return {
                terminate: {
                    end_date: { required: requiredIf(function() { return this.terminate.mode != "immediately" }) },
                    termination_reason : { maxLengthValue: maxLength(5000) },
                    mode: { required },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
        <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
            <ul class="list-unstyled">
                <li v-for="error of errors">
                    {{ error }}
                </li>
            </ul>
        </Message>
        
        <Message severity="error" :closable="false" v-if="rental.termination && rental.status == 'current'">
            {{ $t('rent.rental_is_already_being_terminated') }}
        </Message>
        
        <div class="grid mt-1">
            <div class="col col-12">
                <div class="card">
                    <h3 class="mb-5 text-color">
                        {{ $t('rent.rental_agreement_no_of', [rental.full_number, rental.document_date]) }}
                    </h3>
                    
                    <div class="grid mt-3">
                        <div class="col-12 xl:col-6">
                            <div class="grid">
                                <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                    <span class="font-medium">{{ $t('rent.tenant') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ rental.tenant.name }} <Badge :value="getValueLabel('tenant_types', rental.tenant.type)" class="font-normal" severity="info"></Badge>
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            </div>
                        </div>
                        
                        <div class="col-12 xl:col-6">
                            <div class="grid">
                                <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                    <span class="font-medium">{{ $t('rent.estate') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ rental.item.name }} <Badge :value="getValueLabel('item_types', rental.item.type)" class="font-normal" severity="info"></Badge>
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                                
                                <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                    <span class="font-medium">{{ $t('rent.period') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    {{ rental.start }} - 
                                    <span v-if="rental.period == 'indeterminate'">
                                        <span style="text-transform: lowercase">{{ $t('rent.indeterminate') }}</span>
                                    </span>
                                    <span v-if="rental.period == 'month'">
                                        {{ rental.end }}<br/>({{ rental.months }} {{ p(rental.months, $t('rent.1months'), $t('rent.2months'), $t('rent.3months')) }})
                                    </span>
                                    <span v-if="rental.period == 'date'">
                                        {{ rental.end }}
                                    </span>
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                                
                                <div class="col-fixed pt-0 pb-1" style="width: 186px">
                                    <span class="font-medium">{{ $t('rent.terminate') }}:</span>
                                </div>
                                <div class="col-12 sm:col-7 pt-0 pb-1">
                                    <span v-if="rental.termination_period == 'days'">
                                        {{ rental.termination_days }} {{ p(rental.termination_days, $t('rent.1days'), $t('rent.2days'), $t('rent.3days')) }}
                                    </span>
                                    <span v-if="rental.termination_period == 'months'">
                                        {{ rental.termination_months }} {{ p(rental.termination_months, $t('rent.1months'), $t('rent.2months'), $t('rent.3months')) }}
                                    </span>
                                </div>
                                <div class="col-12 pb-2 pt-2"><div class="border-bottom-1 border-gray-200"></div></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-fluid">
                        <div class="formgrid grid">
                            <div class="field col-12 md:col-4 mb-4">
                                <label for="mode" class="block text-900 font-medium mb-2">{{ $t('rent.terminate_mode') }}</label>
                                <Dropdown id="mode" v-model="terminate.mode" :options="terminate_mode" optionLabel="name" optionValue="id" :class="{'p-invalid' : v.terminate.mode.$error}" :placeholder="$t('rent.select_terminate_mode')" class="w-full" :disabled="loading || saving || block"/>
                                <div v-if="v.terminate.mode.$error">
                                    <small class="p-error">{{ v.terminate.mode.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12 md:col-4 mb-4">
                                <label for="end_date" v-required class="block text-900 font-medium mb-2">{{ $t('rent.termination_time') }}</label>
                                <Calendar id="end_date" v-model="terminate.end_date" :class="{'p-invalid' : v.terminate.end_date.$error}" :placeholder="$t('rent.termination_time')" showIcon :disabled="loading || saving || block || terminate.mode == 'immediately'"/>
                                <div v-if="v.terminate.end_date.$error">
                                    <small class="p-error">{{ v.terminate.end_date.$errors[0].$message }}</small>
                                </div>
                                <small>{{ $t('rent.terminate_date_info') }}</small>
                            </div>
                            
                            <div class="field col-12 mb-4">
                                <label for="reason" class="block text-900 font-medium mb-2">{{ $t('rent.termination_reason') }}</label>
                                <Textarea id="reason" type="text" :placeholder="$t('rent.termination_reason')" rows="3" class="w-full" :class="{'p-invalid' : v.terminate.termination_reason.$error}" v-model="terminate.termination_reason" :disabled="loading || saving || block"/>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <div v-if="loading">
                            <ProgressSpinner style="width: 25px; height: 25px"/>
                        </div>
                        
                        <div class="flex justify-content-between align-items-center">
                            <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                            <Button type="submit" :label="$t('rent.terminate_contract_short')" v-if="!loading" :disabled="block" :loading="saving" iconPos="right" icon="pi pi-delete-left" class="p-button-danger w-auto text-center"></Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <Dialog v-model:visible="displayConfirmation" :closable="false" :style="{ width: '450px' }" :modal="true">
        <div class="text-center" style="color: var(--red-600)">
            <i class="pi pi-exclamation-triangle" style="font-size: 4rem" />
            <p class="line-height-3 mt-3">
                {{ $t('rent.you_intend_to_terminate_the_lease_agreement_number') }}<br/><span class="font-semibold">{{ rental.full_number }}</span> {{ $t("rent.of_the_date") }}: {{ rental.document_date }}.
            </p>
            <p class="line-height-3 mt-3">
                {{ $t('rent.rental_end_date_will_be') }}:<br/>{{ terminate_modal.end_date }}
            </p>
        </div>
        <template #footer>
            <Button :label="$t('app.cancel')" icon="pi pi-times" @click="closeConfirmation" class="p-button-secondary" autofocus />
            <Button :label="$t('rent.terminate_contract')" icon="pi pi-check" @click="submitForm" class="p-button-danger"  />
        </template>
    </Dialog>
</template>