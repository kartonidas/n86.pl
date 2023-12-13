<script>
    import Address from '@/views/app/_partials/Address.vue'
    import { getValueLabel, getValues, p } from '@/utils/helper'
    export default {
        components: { Address },
        data() {
            return {
                getValueLabel,
                p,
            }
        },
        props: {
            object: { type: Object },
        }
    };
</script>

<template>
    <Badge :value="getValueLabel('tenant_types', object.tenant.type)" class="font-normal" severity="info"></Badge>
    <div class="font-medium text-lg mb-2 mt-1">
        {{ object.tenant.name }}
    </div>
    <div class="mb-2">
        <span class="font-medium">{{ $t('tenants.address') }}: </span> <i><Address :object="object.tenant"/></i>
    </div>
    
    <span v-if="object.tenant.type == 'person' && (object.tenant.pesel || object.tenant.document_number)">
        <div class="mb-2" v-if="object.tenant.pesel">
            <span class="font-medium">{{ $t('tenants.pesel') }}: </span> <i>{{ object.tenant.pesel }}</i>
        </div>
        <div class="mb-2" v-if="object.tenant.document_number">
            <span class="font-medium">{{ $t('tenants.document_number') }}: </span> <i>{{ object.tenant.document_number }}</i>
        </div>
    </span>
    <span v-if="object.tenant.type == 'firm' && (object.tenant.nip || object.tenant.regon)">
        <div class="mb-2" v-if="object.tenant.nip">
            <span class="font-medium">{{ $t('tenants.nip') }}: </span> <i>{{ object.tenant.nip }}</i>
        </div>
        <div class="mb-2" v-if="object.tenant.regon">
            <span class="font-medium">{{ $t('tenants.regon') }}: </span> <i>{{ object.tenant.regon }}</i>
        </div>
    </span>
    
    <div class="mb-2">
        <span class="font-medium">{{ $t('rent.period') }}: </span>
        <i>
            {{ object.start }} - 
            <span v-if="object.period == 'indeterminate'">
                <span style="text-transform: lowercase">{{ $t('rent.indeterminate') }}</span>
            </span>
            <span v-if="object.period == 'month'">
                {{ object.end }} ({{ object.months }} {{ p(object.months, $t('rent.1months'), $t('rent.2months'), $t('rent.3months')) }})
            </span>
            <span v-if="object.period == 'date'">
                {{ object.end }}
            </span>
        </i>
    </div>
        
    <div class="mb-2">
        <span class="font-medium">{{ $t('rent.rent') }}: </span> <i>{{ numeralFormat(object.rent, '0.00') }}</i>
    </div>
    <div v-if="object.payment == 'cyclical'">
        <div class="mb-2">
            <span class="font-medium">{{ $t('rent.payment_day') }}: </span> <i>{{ object.payment_day }}{{ $t("rent.payment_day_postfix") }} {{ $t("rent.each_month") }}</i>
        </div>
    </div>
</template>