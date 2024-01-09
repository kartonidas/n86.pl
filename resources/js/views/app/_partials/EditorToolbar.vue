<template>
    <span class="ql-formats">
        <select class="ql-header" defaultValue="0">
            <option value="1">Heading</option>
            <option value="2">Subheading</option>
            <option value="0">Normal</option>
        </select>
    </span>
    <span class="ql-formats">
        <button class="ql-bold" type="button"></button>
        <button class="ql-italic" type="button"></button>
        <button class="ql-underline" type="button"></button>
    </span>
    <span class="ql-formats">
        <button class="ql-list" value="ordered" type="button"></button>
        <button class="ql-list" value="bullet" type="button"></button>
        <select class="ql-align">
            <option defaultValue></option>
            <option value="center"></option>
            <option value="right"></option>
            <option value="justify"></option>
        </select>
    </span>
    <span class="ql-formats">
        <button class="ql-clean" type="button"></button>
    </span>
</template>