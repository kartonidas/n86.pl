<script>
    import { ref, reactive, computed } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, requiredIf, helpers, maxLength } from '@/utils/i18n-validators'
    import { getValues, getValueLabel } from '@/utils/helper'
    
    import UserService from '@/service/UserService'
    
    export default {
        emits: ['submit-form', 'back'],
        watch: {
            "notification.type" : function() {
                this.configFormByType()
            }
        },
        setup() {
            const userService = new UserService()
            return {
                v: useVuelidate(),
                userService,
                getValueLabel
            }
        },
        data() {
            return {
                types : getValues("notifications.types"),
                modes : getValues("notifications.modes"),
                showDaysDropdown : false,
                days: [],
            }
        },
        props: {
            notification: { type: Object },
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            source: { type: String, default: 'new' },
        },
        beforeMount() {
            this.configFormByType()
        },
        methods: {
            configFormByType() {
                this.types.forEach((type) => {
                    if (type.id == this.notification.type) {
                        this.showDaysDropdown = type.days
                        if (type.days) {
                            let allowedDays = []
                            this.days = []
                            type.allowed_days.forEach((day) => {
                                this.days.push({"id" : day, "name" : day})
                                allowedDays.push(day)
                            })
                            
                            if(allowedDays.indexOf(this.notification.days) === -1)
                                this.notification.days = 14
                        }
                        
                        let allowedModes = []
                        this.modes = []
                        type.modes.forEach((allowedMode) => {
                            getValues("notifications.modes").forEach((mode) => {
                                if (mode.id == allowedMode)
                                {
                                    this.modes.push(mode)
                                    allowedModes.push(mode.id)
                                }
                            })
                        })
                        if(allowedModes.indexOf(this.notification.mode) === -1)
                            this.notification.mode = this.modes[0].id
                    }
                })
            },
            
            async submitForm() {
                let notification = Object.assign({}, this.notification);
                    
                const result = await this.v.$validate()
                if (result)
                    this.$emit('submit-form', notification)
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$emit('back')
            }
        },
        validations () {
            return {
                notification: {
                    type: { required },
                    days: { required: requiredIf(function() { return this.showDaysDropdown }) },
                    mode: { required },
                }
            }
        },
    };
</script>

<template>
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
                    <div class="field col-12 md:col-4 mb-4" :class="showDaysDropdown ? 'md:col-4' : 'md:col-6'">
                        <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('notifications.type') }}</label>
                        <Dropdown id="type" v-model="notification.type" :options="types" :class="{'p-invalid' : v.notification.type.$error}" optionLabel="name" optionValue="id" :placeholder="$t('notifications.type')" class="w-full" :disabled="saving || loading || source == 'update'"/>
                        <div v-if="v.notification.type.$error">
                            <small class="p-error">{{ v.notification.type.$errors[0].$message }}</small>
                        </div>
                    </div>
                    <div class="field col-12 md:col-4 mb-4" v-if="showDaysDropdown">
                        <label for="days" v-required class="block text-900 font-medium mb-2">{{ $t('notifications.days') }}</label>
                        <Dropdown id="days" v-model="notification.days" :options="days" :class="{'p-invalid' : v.notification.days.$error}" optionLabel="name" optionValue="id" :placeholder="$t('notifications.days')" class="w-full" :disabled="saving || loading"/>
                        <div v-if="v.notification.days.$error">
                            <small class="p-error">{{ v.notification.days.$errors[0].$message }}</small>
                        </div>
                    </div>
                    <div class="field col-12 md:col-4 mb-4" :class="showDaysDropdown ? 'md:col-4' : 'md:col-6'">
                        <label for="mode" v-required class="block text-900 font-medium mb-2">{{ $t('notifications.mode') }}</label>
                        <Dropdown id="mode" v-model="notification.mode" :options="modes" :class="{'p-invalid' : v.notification.mode.$error}" optionLabel="name" optionValue="id" :placeholder="$t('notifications.mode')" class="w-full" :disabled="saving || loading"/>
                        <div v-if="v.notification.mode.$error">
                            <small class="p-error">{{ v.notification.mode.$errors[0].$message }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-footer">
            <div v-if="source != 'new' && loading">
                <ProgressSpinner style="width: 25px; height: 25px"/>
            </div>
            
            <div class="flex justify-content-between align-items-center">
                <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
            </div>
        </div>
    </form>
</template>