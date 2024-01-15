<script setup>
import { ref, onBeforeMount, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useLayout } from '@/layout/composables/layout';

const route = useRoute();

const { layoutConfig, layoutState, setActiveMenuItem, onMenuToggle } = useLayout();

const props = defineProps({
    item: {
        type: Object,
        default: () => ({})
    },
    index: {
        type: Number,
        default: 0
    },
    root: {
        type: Boolean,
        default: true
    },
    parentItemKey: {
        type: String,
        default: null
    },
    counters: {
        type: Object,
        default: () => ({})
    },
});

const isActiveMenu = ref(false);
const itemKey = ref(null);

onBeforeMount(() => {
    itemKey.value = props.parentItemKey ? props.parentItemKey + '-' + props.index : String(props.index);

    const activeItem = layoutState.activeMenuItem;

    isActiveMenu.value = activeItem === itemKey.value || activeItem ? activeItem.startsWith(itemKey.value + '-') : false;
});

watch(
    () => layoutConfig.activeMenuItem.value,
    (newVal) => {
        isActiveMenu.value = newVal === itemKey.value || newVal.startsWith(itemKey.value + '-');
    },
);

const itemClick = (event, item) => {
    if (item.disabled) {
        event.preventDefault();
        return;
    }

    const { overlayMenuActive, staticMenuMobileActive } = layoutState;

    if ((item.to || item.url) && (staticMenuMobileActive.value || overlayMenuActive.value)) {
        onMenuToggle();
    }

    if (item.command) {
        item.command({ originalEvent: event, item: item });
    }

    const foundItemKey = item.items ? (isActiveMenu.value ? props.parentItemKey : itemKey) : itemKey.value;

    setActiveMenuItem(foundItemKey);
};

const checkActiveRoute = (item) => {
    if (item.regex != undefined) {
        if ((route.path).match(item.regex)) 
            return true
    }
    
    return route.path === item.to;
};

const getCounter = (counter) => {
    let value;
    props.counters.forEach((c) => {
        if (c.id == counter) {
            value = c.$value;
            return;
        }
    });
    return value;
}

</script>

<template>
    <li :class="{ 'layout-root-menuitem': root, 'active-menuitem': isActiveMenu }" v-if="item.access == undefined || item.access">
        <div v-if="root && item.visible !== false" class="layout-menuitem-root-text">{{ item.label }}</div>
        <a v-if="(!item.to || item.items) && item.visible !== false" :href="item.url" @click="itemClick($event, item, index)" :class="item.class" :target="item.target" tabindex="0">
            <i :class="item.icon" class="layout-menuitem-icon"></i>
            <span class="layout-menuitem-text">{{ item.label }}</span>
            <i class="pi pi-fw pi-angle-down layout-submenu-toggler" v-if="item.items"></i>
        </a>
        <router-link v-if="item.to && !item.items && item.visible !== false" @click="itemClick($event, item, index)" class="justify-content-between" :class="[item.class, { 'active-route': checkActiveRoute(item) }]" tabindex="0" :to="item.to">
            <div>
                <i :class="item.icon" class="layout-menuitem-icon"></i>
                <span class="layout-menuitem-text">{{ item.label }}</span>
            </div>
            <template v-if="item.count" v-for="count in item.count">
                <Badge v-if="getCounter(count.index)" :value="getCounter(count.index)" class="p-badge-secondary"></Badge>
            </template>
        </router-link>
        <Transition v-if="item.items && item.visible !== false" name="layout-submenu">
            <ul v-show="root ? true : isActiveMenu" class="layout-submenu">
                <menu-item v-for="(child, i) in item.items" :key="child" :index="i" :item="child" :parentItemKey="itemKey" :root="false" :counters="counters"></menu-item>
            </ul>
        </Transition>
    </li>
</template>
