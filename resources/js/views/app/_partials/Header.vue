<script>
    import { getValueLabel, hasAccess } from '@/utils/helper'
    import Address from '@/views/app/_partials/Address.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { Address },
        setup() {
            return {
                hasAccess,
                getValueLabel
            }
        },
        data() {
            return {
                itemLockConfirmationModal: false,
                itemLockConfirmationDisabled: false
            }
        },
        props: {
            object: { type: Object },
            type: { type: String },
            showEditButton: { type: Boolean, default: false },
            class: { type: String, default: "" }
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
            },
            showItemLockConfirmationModal() {
                this.itemLockConfirmationModal = true
            },
            closeItemLockConfirmationModal() {
                this.itemLockConfirmationModal = false
            },
            confirmItemChangeLock() {
                const itemService = new ItemService()
                this.itemLockConfirmationDisabled = true
                if (this.object.mode == "locked") {
                    itemService.unlock(this.object.id)
                        .then(
                            (response) => {
                                this.object.mode = "normal"
                                this.itemLockConfirmationDisabled = false
                                this.itemLockConfirmationModal = false
                                this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.item_unlocked'), life: 3000 });
                            },
                            (errors) => {
                                this.itemLockConfirmationDisabled = false
                                this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            },
                        )
                } else {
                    itemService.lock(this.object.id)
                        .then(
                            (response) => {
                                this.object.mode = "locked"
                                this.itemLockConfirmationDisabled = false
                                this.itemLockConfirmationModal = false
                                this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.item_locked'), life: 3000 });
                            },
                            (errors) => {
                                this.itemLockConfirmationDisabled = false
                                this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                            },
                        )
                }
            }
        }
    };
</script>

<template>
    <div class="flex align-items-center" :class="class">
        <div class="w-full">
            <Badge :value="getType()" class="font-normal" severity="info"></Badge>
            <h3 class="mt-2 mb-1 text-color" :class="class">
                <template v-if="type == 'item'">
                    <i class="pi pi-lock" v-if="object.mode == 'locked'" v-tooltip.top="$t('items.locked')"></i>
                </template>
                {{ object.name }}
            </h3>
            <div>
                <Address :object="object" />
            </div>
        </div>
        <div class="text-right" style="width: 120px;" v-if="showEdit()">
            <Button icon="pi pi-pencil" @click="edit" v-tooltip.left="$t('app.edit')"></Button>
            
            <template v-if="type == 'item'">
                <Button v-if="object.mode == 'locked'" icon="pi pi-unlock" v-tooltip.bottom="$t('items.unlock')" severity="warning" class="ml-1" @click="showItemLockConfirmationModal" />
                <Button v-if="object.mode == 'normal'" icon="pi pi-lock" v-tooltip.bottom="$t('items.lock')" severity="danger" class="ml-1" @click="showItemLockConfirmationModal" />
            </template>
        </div>
    </div>
    
    <Dialog v-model:visible="itemLockConfirmationModal" :closable="false" :style="{ width: '450px' }" :modal="true">
        <div class="text-center" style="color: var(--red-600)">
            <i class="pi pi-exclamation-triangle" style="font-size: 4rem" />
            <p class="line-height-3 mt-3">
                <span v-if="object.mode == 'locked'">
                    {{ $t('items.unlock_confirmation_text') }}
                </span>
                <span v-else>
                    {{ $t('items.lock_confirmation_text') }}
                </span>
            </p>
        </div>
        <template #footer>
            <Button :label="$t('app.no')" icon="pi pi-times" @click="closeItemLockConfirmationModal" class="p-button-secondary" autofocus/>
            <Button :label="object.mode == 'locked' ? $t('items.unlock') : $t('items.lock')" :disabled="itemLockConfirmationDisabled" icon="pi pi-check" @click="confirmItemChangeLock" class="p-button-danger"/>
        </template>
    </Dialog>
</template>