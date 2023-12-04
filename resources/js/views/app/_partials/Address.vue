<script>
    export default {
        props: {
            object: { type: Object },
            newline: { type: Boolean },
            emptyChar: {
                type: String,
                default: "-"
            }
        },
    };
</script>

<template>
    <span v-if="object.street || object.zip || object.city || object.country">
        <span v-if="object.street">
            {{ object.street }}
            <span v-if="object.house_no">{{ object.house_no }}</span>
            <span v-if="object.apartment_no">
                <span v-if="object.house_no">/</span>
                <span v-else></span>
                {{ object.apartment_no }}
            </span>
        </span>
        
        <span v-if="object.zip || object.city">
            <span v-if="object.street">, <br v-if="newline"/></span>
            <span v-if="object.zip">{{ object.zip }}</span> <span v-if="object.city">{{ object.city }}</span>
        </span>
        
        <span v-if="object.country">
            <span v-if="object.street || object.zip || object.city">,</span>
            {{ object.country }}
        </span>
    </span>
    <span v-else>{{ emptyChar }}</span>
</template>