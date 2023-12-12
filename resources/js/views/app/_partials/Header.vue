<script>
    import { getValueLabel, hasAccess } from '@/utils/helper'
    import Address from '@/views/app/_partials/Address.vue'
    
    export default {
        components: { Address },
        setup() {
            return {
                hasAccess,
                getValueLabel
            }
        },
        props: {
            object: { type: Object },
            type: { type: String },
            showEditButton: { type: Boolean, default: false }
        },
        methods: {
            showEdit() {
                if (this.showEditButton) {
                    switch(this.type) {
                        case "item":
                            return hasAccess('item:update')
                        break;
                        case "tenant":
                            return hasAccess('tenant:update')
                        break;
                        case "customer":
                            return hasAccess('customer:update')
                        break;
                    }
                }
                return false;
            },
            getType() {
                let type = null
                switch(this.type) {
                    case "item":
                        type = "item_types"
                    break;
                    case "tenant":
                        type = "tenant_types"
                    break;
                    case "customer":
                        type = "customer_types"
                    break;
                }
                return getValueLabel(type, this.object.type)
            },
            edit() {
                switch (this.type) {
                    case "item":
                        this.$router.push({name: 'item_edit', params: { itemId : this.$route.params.itemId }})
                    break;
                    
                    case "tenant":
                        this.$router.push({name: 'tenant_edit', params: { tenantId : this.object.id }})
                    break;
                    
                    case "customer":
                        this.$router.push({name: 'customer_edit', params: { customerId : this.$route.params.customerId }})
                    break;
                }
            }
        }
    };
</script>

<template>
    <div class="flex align-items-center">
        <div class="w-full">
            <Badge :value="getType()" class="font-normal" severity="info"></Badge>
            <h3 class="mt-2 mb-1 text-color">{{ object.name }}</h3>
            <div>
                <Address :object="object" />
            </div>
        </div>
        <div class="text-right" v-if="showEdit">
            <Button icon="pi pi-pencil" @click="edit" v-tooltip.left="$t('app.edit')"></Button>
        </div>
    </div>
    
    <div v-if="type == 'customexxr'">
        <div class="flex align-items-center">
            <div class="w-full">
                <Badge :value="getValueLabel('customer_types', customer.type)" class="font-normal" severity="info"></Badge>
                <h3 class="mt-2 mb-1 text-color">{{ customer.name }}</h3>
                <div>
                    <Address :object="customer" />
                </div>
            </div>
            <div class="text-right" v-if="showEditButton && hasAccess('customer:update')">
                <Button icon="pi pi-pencil" @click="editCustomer" v-tooltip.left="$t('app.edit')"></Button>
            </div>
        </div>
    </div>
    
    <div v-if="type == 'tenanxxt'">
        <div class="flex align-items-center">
            <div class="w-full">
                <Badge :value="getValueLabel('tenant_types', object.type)" class="font-normal" severity="info"></Badge>
                <h3 class="mt-2 mb-1 text-color">{{ object.name }}</h3>
                <div>
                    <Address :object="object" />
                </div>
            </div>
            <div class="text-right" v-if="showEditButton && hasAccess('tenant:update')">
                <Button icon="pi pi-pencil" @click="editTenant" v-tooltip.left="$t('app.edit')"></Button>
            </div>
        </div>
    </div>
</template>