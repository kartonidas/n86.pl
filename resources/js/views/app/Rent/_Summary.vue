<script>
    import { getValueLabel, getResponseErrors, setMetaTitle } from '@/utils/helper'
    import Address from '@/views/app/_partials/Address.vue'
    import InfoRentalObj from './_InfoRentalObject.vue'
    
    export default {
        components: { Address, InfoRentalObj },
        emits: ['submit-form', 'back'],
        setup() {
            return {
                getValueLabel
            }
        },
        props: {
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            item: { type: Object },
            rent: { type: Object },
            tenant: { type: Object },
        },
        methods: {
            back() {
                this.$emit('back')
            },
            
            rentConfirm() {
                this.$emit('submit-form')
            }
        }
    }
</script>

<template>
    <div class="mb-5">
        <InfoRentalObj type="tenant" :object="tenant"/>
    </div>
    <div class="mb-5">
        <InfoRentalObj type="item" :object="item"/>
    </div>
    <div class="mb-5">
        <InfoRentalObj type="rent" :object="rent"/>
    </div>
    
    <div class="form-footer">
        <div class="flex justify-content-between align-items-center">
            <Button type="button" :label="$t('app.back')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
            <Button type="submit" :label="$t('app.save')" :loading="saving" @click="rentConfirm" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
        </div>
    </div>
</template>