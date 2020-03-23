<!--
CST226 Jon Ronn
CST230 Anthony Smith
CST232 Matt Smith
A Vue(view) component (in table form) for
the Vehicle webpage and database
CWEB280 Assign03
---->

<template>
    <div>
        <!--https://bootstrap-vue.js.org/docs/components/table
        'show-empty' leaves the message 'There are no records to show' when no records present
        'no-provider-sorting' leaves the table columns to sort the information rather than needing to
        sort it elsewhere in a function for example
        :items used to source the items for the table (can be an array or function)
        :fields used to source the column names
        'striped' stripes the table rows for visibility
        'hover' highlights the row your mouse is hovering on for visibility
        'bordered' adds a border around the table for distinction
        'head-variant' set to dark for distinction
        'primary key' set because needed for transition effects
        :busy used to control the busy state of the table
        :tbody-transition-props sets the table body transition property
        -->

        <b-table id="vehicle-table" ref="vehicle-table" show-empty no-provider-sorting :items="getSomeVehicles" :fields="fields"
                 striped hover bordered head-variant="dark" primary-key="vehicleID" :busy="isBusy" :tbody-transition-props="transProps">
            <!--Custom render when the table is busy;  overrides the table render
            with the busy spinner (Centered in a div class margin top and bottom * 0.5)
            https://bootstrap-vue.js.org/docs/components/table/#table-busy-state-->
            <template v-slot:table-busy>
                <div class="text-center my-2">
                    <b-spinner></b-spinner>
                </div>
            </template>
            <!--Add a row above the table head where we can add the 'Add Vehicle' button
            use a template with custom table heads which span the table-->
            <template v-slot:thead-top="data">
                <b-tr >
                    <b-th colspan="5" style="text-align: center; font-size: large">Vehicle Table</b-th>
                    <b-th style="text-align: center; width: 5%">
                        <!--calls the @new function on the UI
                        if the table is busy, the button disappears-->
                        <b-button v-if="!isBusy" size="sm" variant="primary" class="fas fa-plus-square" title="Add New Vehicle" @click.stop="$emit('new')"></b-button>
                    </b-th>
                </b-tr>
            </template>
            <!--Template using v-slot for head of vehicleID, used to shrink the column width down-->
            <template v-slot:head(vehicleID)="data">
                <div style="width: 5%">
                    <b>{{data.label}}</b>
                </div>
            </template>
            <!-- a template using v-slot for the 'vehicleID' cells used for adding the
             bold to the vehicle ID with any row that has data on it, and to shrink the width down-->
            <template v-slot:cell(vehicleID)="data">
                <div style="width: 5%">
                    <b>{{data.value}}</b>
                </div>
            </template>
            <!--a template for the 'actions' column to add the edit button to each row with data in it
            set to size of 'sm' so that it doesn't expand the row with a bunch of empty space
            'variant' set to 'primary' so that is blue, which is nice-->
            <template v-slot:cell(actions)="row" >
                <div style="text-align: center;">
                    <b-button size="sm" variant="primary" class="fas fa-edit" @click.stop="$emit('edit',row.item)" title="Edit Vehicle"></b-button>
                </div>
            </template>
        </b-table>
    </div>
</template>
<script>
    module.exports = {
        data() {
            return {
                //Fields used to define the columns in the table
                //Sortable set to true allows local column sorting on the table
                fields:
                    [{  key:'vehicleID',
                        label: 'ID',
                        sortable: true},
                    {   key:'make',
                        sortable:true},
                    {   key:'model',
                        sortable:true},
                    {   key:'type',
                        sortable:true},
                    {   key:'year',
                        sortable:true},
                    {   key:'actions'}],
                //Control the busy state of the table with this
                isBusy: false,
                //Transition group property used in table
                transProps: {
                    //Tied to the CSS style when table rows move
                    name: 'flip-list'
                }
            }
        },
        methods:{
            //Called from the b-table to populate the table records from the API
            getSomeVehicles () {
                let promise = axios.get('vehicle-api.php');
                //Set the busy state
                this.isBusy = true;
                return promise.then(response=>
                {   //Populate the :items on the table with the response data
                    let items = response.data;
                    //Disable the busy state
                    this.isBusy = false;
                    //Return the items to the table
                    return(items)
                }).catch(errors => {
                    //Disable busy state
                    this.isBusy = false;
                    //Not used anywhere?
                    return errors.data;
                })
            }
        }
    }
</script>
<style type="text/css" scoped>
    /**Style used for transition effect
    https://vuejs.org/v2/guide/transitions.html#List-Move-Transitions**/
    .flip-list-move {
        transition: transform 0.5s;
    }
</style>