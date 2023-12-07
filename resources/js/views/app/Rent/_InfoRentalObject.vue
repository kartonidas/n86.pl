<script>
    import { getValueLabel } from '@/utils/helper'
    import Address from '@/views/app/_partials/Address.vue'
    export default {
        components: { Address },
        setup() {
            return {
                getValueLabel
            }
        },
        props: {
            object: { type: Object, required: true },
            type: { type: String, required: true },
        },
    };
</script>

<template>
    <div v-if="type == 'tenant'">
        <div class="font-medium text-lg mb-2">
            {{ object.name }} (<span style="text-transform: lowercase">{{ getValueLabel('tenant_types', object.type) }}</span>),
        </div>
        
        <div class="mb-1">
            {{ $t('tenants.address') }}: <Address :object="object"/>
        </div>
        
        <span v-if="object.type == 'person' && (object.pesel || object.document_number)">
            <div class="mb-1" v-if="object.pesel">
                {{ $t('tenants.pesel') }}: {{ object.pesel }}
            </div>
            <div class="mb-1" v-if="object.document_number">
                {{ $t('tenants.document_number') }}: {{ object.document_number }} <span v-if="object.document_type || object.document_extra" style="text-transform: lowercase">({{ getValueLabel('customer.documents', object.document_type) }}<span v-if="object.document_type && object.document_extra">, </span>{{ object.document_extra }})</span>
            </div>
        </span>
        <span v-if="object.type == 'firm' && (object.nip || object.regon)">
            <div class="mb-1" v-if="object.nip">
                {{ $t('tenants.nip') }}: {{ object.nip }}
            </div>
            <div class="mb-1" v-if="object.regon">
                {{ $t('tenants.regon') }}: {{ object.regon }}
            </div>
        </span>
    </div>
    
    <div v-if="type == 'item'">
        <div class="font-medium text-lg mb-2">
            {{ object.name }} (<span style="text-transform: lowercase">{{ getValueLabel('item_types', object.type) }}</span>),
        </div>
        <div class="mb-1">
            {{ $t('items.address') }}: <Address :object="object"/>
        </div>
        <div class="mb-1">
            {{ $t('items.area') }}: {{ numeralFormat(object.area, '0.00') }} (m2)
        </div>
        
        <div class="mb-1" v-if="object.num_rooms">
            {{ $t('items.number_of_rooms') }}: {{ object.num_rooms }}
        </div>
    </div>
    
    <div v-if="type == 'rent'">
        <div class="mb-1">
            {{ $t('rent.start_date') }}: {{ object.start_date }}
        </div>
        
        <div class="mb-1">
            {{ $t('rent.period') }}:
            
            <span v-if="object.period == 'indeterminate'">
                <span style="text-transform: lowercase">{{ $t('rent.indeterminate') }}</span>
            </span>
            <span v-if="object.period == 'month'">
                {{ object.months }} {{ $t('rent.months') }}
            </span>
            <span v-if="object.period == 'date'">
                {{ object.end_date }}
            </span>
        </div>
        <div class="mb-1">
            {{ $t('rent.termination') }}:
            <span v-if="object.termination_period == 'months'" style="text-transform: lowercase">
                {{ object.termination_months }} {{ $t('rent.months') }}
            </span>
            <span v-if="object.termination_period == 'days'" style="text-transform: lowercase">
                {{ object.termination_days }} {{ $t('rent.days') }}
            </span>
        </div>
        <div class="mb-1">
            {{ $t('rent.deposit') }}: {{ numeralFormat(object.deposit, '0.00') }}
        </div>
        <div class="mb-1">
            {{ $t('rent.payment') }}: {{ getValueLabel('rental.payments', object.payment) }}
        </div>
        <div class="mb-1">
            {{ $t('rent.rent') }}: {{ numeralFormat(object.rent, '0.00') }}
        </div>
        <div v-if="object.payment == 'cyclical'">
            <div class="mb-1" v-if="object.first_month_different_amount">
                {{ $t('rent.first_month_different_amount') }}: {{ numeralFormat(object.first_month_different_amount_value, '0.00') }}
            </div>
            <div class="mb-1" v-if="object.last_month_different_amount">
                {{ $t('rent.last_month_different_amount') }}: {{ numeralFormat(object.last_month_different_amount_value, '0.00') }}
            </div>
            <div class="mb-1">
                {{ $t('rent.payment_day') }}: {{ object.payment_day }}
            </div>
        </div>
        <div class="mb-1">
            {{ $t('rent.first_payment_date') }}: {{ object.first_payment_date }}
        </div>
        <div class="mb-1">
            {{ $t('rent.number_of_people') }}: {{ object.number_of_people }}
        </div>
    </div>
</template>