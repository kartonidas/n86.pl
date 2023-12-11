<script>
    import Address from '@/views/app/_partials/Address.vue'
    import { getValueLabel } from '@/utils/helper'
    
    export default {
        components: { Address },
        setup() {
            return {
                getValueLabel
            }
        },
        data() {
            return {
                items: [
                    { label: 'Podstawowe informacje', icon: 'pi pi-fw pi-building', route: { name: 'item_show' }, index: 0 },
                    {
                        label: 'Wynajem',
                        icon: 'pi pi-fw pi-users',
                        index: 1,
                        items: [
                            { label: 'Rezerwacje', icon: 'pi pi-fw pi-list', route: { name: 'item_show_bills' }, index: 11 },
                            { label: 'Historia', icon: 'pi pi-fw pi-history', route: { name: 'item_show_bills' }, index: 12 },
                        ]
                    },
                    {
                        label: 'Opłaty',
                        icon: 'pi pi-fw pi-wallet',
                        index: 2,
                        items: [
                            { label: 'Rachunki', icon: 'pi pi-fw pi-money-bill', route: { name: 'item_show_bills' }, index: 2 },
                            { label: 'Opłaty stałe', icon: 'pi pi-fw pi-wallet', route: { name: 'items' }, index: 3 },
                        ]
                    },
                    
                    
                    { label: 'Statystyki', icon: 'pi pi-fw pi-chart-line', route: { name: 'items' }, index: 4 },
                ]
            }
        },
        
        props: {
            activeIndex: { type: String },
            item: { type: Object },
            showEditButton: { type : Boolean, default : true },
            showDivider: { type : Boolean, default : false }
        },
        
        methods: {
            editItem() {
                this.$router.push({name: 'item_edit', params: { itemId : this.$route.params.itemId }})
            },
        }
    }
</script>

<template>
    <div>
        <Menubar :model="items">
            <template #item="{ item, props, hasSubmenu }">
                <router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
                    <a :href="href" v-bind="props.action" @click="navigate" :class="[parseInt(item.index) == parseInt(activeIndex) ? 'text-color-primary' : 'text-color-secondary']">
                        <span :class="item.icon" />
                        <span class="ml-2">{{ item.label }}</span>
                    </a>
                </router-link>
                <a v-else  :href="item.url" :target="item.target" v-bind="props.action" :class="[parseInt(item.index) == parseInt(activeIndex) ? 'text-color-primary' : 'text-color-secondary']">
                    <span :class="item.icon" />
                    <span class="ml-2">{{ item.label }}</span>
                    <span v-if="hasSubmenu" class="pi pi-fw pi-angle-down ml-2" />
                </a>
            </template>
        </Menubar>
        
        <div class="flex align-items-center mb-0 mt-5">
            <div class="w-full">
                <Badge :value="getValueLabel('item_types', item.type)" class="font-normal" severity="info"></Badge>
                <h3 class="mt-2 mb-1 text-color">{{ item.name }}</h3>
                <div>
                    <Address :object="item" />
                </div>
            </div>
            <div class="text-right" v-if="showEditButton">
                <Button icon="pi pi-pencil" @click="editItem" v-tooltip.left="$t('app.edit')"></Button>
            </div>
        </div>
        
        <Divider v-if="showDivider"/>
    </div>
</template>