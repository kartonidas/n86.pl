<script>
    import { ref } from 'vue';
    import Menu from 'primevue/menu';
    
    export default {
        components: { Menu },
        props: {
            show: { type: String },
            class: { type: String },
            mark: { type: String },
        },
        setup() {
            const menu = ref(null);
            return {
                menu,
            }
        },
        methods: {
            toggleHelpMenu(event) {
                this.menu.toggle(event)
            },
            
            markImportant(category, menuItems) {
                if (this.mark) {
                    let mark = this.mark.split("|")
                    
                    mark.forEach((m) => {
                        let splittedMark = m.split(":")
                        if (splittedMark[0] == category) {
                            menuItems.forEach((mi) => {
                                if (mi.id == splittedMark[1])
                                    mi.class = "font-semibold"
                            })
                        }
                    })
                }
                
                return menuItems
            },
            
            filterItems(menuItems, showItems) {
                if (showItems == "_all")
                    return menuItems
                
                let filteredItems = [];
                menuItems.forEach((i) => {
                    if (i.id == undefined || showItems.indexOf(i.id) !== -1)
                        filteredItems.push(i);
                });
                return filteredItems
            },
            
            getMenuItems() {
                let show = this.show.split("|")
                
                var menuItems = [];
                show.forEach((s) => {
                    let showCategory = s;
                    let showItems = "_all";
                    let splittedShow = s.split(":")
                    if (splittedShow.length == 2)
                    {
                        showCategory = splittedShow[0];
                        showItems = splittedShow[1].split(",")
                    }
                    
                    switch(showCategory) {
                        case "item":
                            let itemMenu = [
                                { label : this.$t('help.items.title'), disabled : true, class : "font-semibold surface-200", icon : "pi pi-building" },
                                { id : "new", label : this.$t('help.items.new_item'), url : this.$t('help.items.new_item_url'), target : "_blank" },
                                { id : "bill", label : this.$t('help.items.new_bill'), url : this.$t('help.items.new_bill_url'), target : "_blank" },
                                { id : "cyclical", label : this.$t('help.items.new_cyclical_fee'), url : this.$t('help.items.new_cyclical_fee_url'), target : "_blank" },
                                { id : "payment", label : this.$t('help.items.payment'), url : this.$t('help.items.payment_url'), target : "_blank" },
                                { id : "fault", label : this.$t('help.items.faults'), url : this.$t('help.items.faults_url'), target : "_blank" },
                                { id : "archive", label : this.$t('help.items.archive_block_item'), url : this.$t('help.items.archive_block_item_url'), target : "_blank" },
                            ];
                            itemMenu = this.filterItems(itemMenu, showItems)
                            this.markImportant(showCategory, itemMenu)
                            menuItems = menuItems.concat(itemMenu);
                        break;
                        
                        case "rental":
                            let rentalMenu = [
                                { label : this.$t('help.rentals.title'), disabled : true, class : "font-semibold surface-200", icon : "pi pi-dollar" },
                                { id : "new", label : this.$t('help.rentals.start_rental'), url : this.$t('help.rentals.start_rental_url'), target : "_blank" },
                                { id : "template", label : this.$t('help.rentals.document_templates'), url : this.$t('help.rentals.document_templates_url'), target : "_blank" },
                                { id : "document", label : this.$t('help.rentals.generate_documents'), url : this.$t('help.rentals.generate_documents_url'), target : "_blank" },
                                { id : "terminate", label : this.$t('help.rentals.terminate'), url : this.$t('help.rentals.terminate_url'), target : "_blank" },
                                { id : "notify", label : this.$t('help.rentals.notifications'), url : this.$t('help.rentals.notifications_url'), target : "_blank" },
                                { id : "history", label : this.$t('help.rentals.history_reservation'), url : this.$t('help.rentals.history_reservation_url'), target : "_blank" },
                                { id : "status", label : this.$t('help.rentals.statuses'), url : this.$t('help.rentals.statuses_url'), target : "_blank" },
                            ];
                            rentalMenu = this.filterItems(rentalMenu, showItems)
                            this.markImportant(showCategory, rentalMenu)
                            menuItems = menuItems.concat(rentalMenu);
                        break;
                        
                        case "settlement":
                            let settlementMenu = [
                                { label : this.$t('help.settlements.title'), disabled : true, class : "font-semibold surface-200", icon : "pi pi-file-pdf" },
                                { id : "register", label : this.$t('help.settlements.sale_registries'), url : this.$t('help.settlements.sale_registries_url'), target : "_blank" },
                                { id : "invoice", label : this.$t('help.settlements.create_invoice'), url : this.$t('help.settlements.create_invoice_url'), target : "_blank" },
                            ];
                            settlementMenu = this.filterItems(settlementMenu, showItems)
                            this.markImportant(showCategory, settlementMenu)
                            menuItems = menuItems.concat(settlementMenu);
                        break;
                        
                        case "user":
                            let userMenu = [
                                { label : this.$t('help.users.title'), disabled : true, class : "font-semibold surface-200", icon : "pi pi-user" },
                                { id : "manage", label : this.$t('help.users.manage'), url : this.$t('help.users.manage_url'), target : "_blank" },
                                { id : "permission", label : this.$t('help.users.permissions'), url : this.$t('help.users.permissions_url'), target : "_blank" },
                            ];
                            userMenu = this.filterItems(userMenu, showItems)
                            this.markImportant(showCategory, userMenu)
                            menuItems = menuItems.concat(userMenu);
                        break;
                        
                        case "package":
                            let packageMenu = [
                                { label : this.$t('help.packages.title'), disabled : true, class : "font-semibold surface-200", icon : "pi pi-box" },
                                { id : "order", label : this.$t('help.packages.order_package'), url : this.$t('help.packages.order_package_url'), target : "_blank" },
                                { id : "prolong", label : this.$t('help.packages.prolong_package'), url : this.$t('help.packages.prolong_package_url'), target : "_blank" },
                                { id : "extend", label : this.$t('help.packages.extend_package'), url : this.$t('help.packages.extend_package_url'), target : "_blank" },
                            ];
                            packageMenu = this.filterItems(packageMenu, showItems)
                            this.markImportant(showCategory, packageMenu)
                            menuItems = menuItems.concat(packageMenu);
                        break;
                        
                        case "tenant_customer":
                            let tenantCustomerMenu = [
                                { label : this.$t('help.tenant_customer.title'), disabled : true, class : "font-semibold surface-200", icon : "pi pi-box" },
                                { id : "tenant", label : this.$t('help.tenant_customer.tenant'), url : this.$t('help.tenant_customer.tenant_url'), target : "_blank" },
                                { id : "customer", label : this.$t('help.tenant_customer.customer'), url : this.$t('help.tenant_customer.customer_url'), target : "_blank" }
                            ];
                            tenantCustomerMenu = this.filterItems(tenantCustomerMenu, showItems)
                            this.markImportant(showCategory, tenantCustomerMenu)
                            menuItems = menuItems.concat(tenantCustomerMenu);
                        break;
                    }
                });
                return menuItems
            },
            
            showMenu() {
                if (this.getMenuItems().length)
                    return true
                return false
            }
        }
    };
</script>

<template>
    <template v-if="showMenu()">
        <div :class="class">
            <Menu ref="menu" :model="getMenuItems()" :popup="true" />
            <Button type="button" class="button py-0 px-2 w-auto" outlined text @click="toggleHelpMenu">
                {{ $t('app.need_help') }}
            </Button>
        </div>
    </template>
</template>