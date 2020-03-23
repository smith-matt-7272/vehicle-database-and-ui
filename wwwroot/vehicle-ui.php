<!DOCTYPE html>

<!--
Jon Ronn
Anthony Smith
Matt Smith
CWEB280 Assignment 03
December 06, 2019

The User Interface which uses vehicleTable.vue table component for a data view,
which gets modified through vehicleInput.vue modal component.
Uses vehicle-api for database access
-->

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- https://bootswatch.com themes: cerulean cosmo cyborg darkly flatly journal litera lumen lux
	materia	minty pulse sandstone simplex sketchy slate solar spacelab superhero united yeti -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootswatch/4.3.1/yeti/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-vue/dist/bootstrap-vue.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/portal-vue/dist/portal-vue.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-vue/dist/bootstrap-vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/http-vue-loader/src/httpVueLoader.js"></script>

    <title>Vehicle UI by CST226 CST230 CST232</title>
</head>
<body>
<div class="jumbotron text-center p-5">
    <h1>Vehicle UI by CST226 CST230 CST232</h1>
</div>

<div class="container" id="managed_by_vue_js" >
    <!--VehicleInput is modal template; uses infoVehicle in UI for vehicle prop in component
    @save call made from save button in modal-->
    <vehicle-input :vehicle="infoVehicle" @save="saveVehicle" ></vehicle-input>
    <!--VehicleTable is table component template; @edit called from action button in each
    record of the table; @new called from action button in top row above table header-->
    <vehicle-table @edit="editVehicle" @new="newVehicle"></vehicle-table>
</div>
<script>
    new Vue({
        el: '#managed_by_vue_js',
        data:{
            //InfoVehicle gets populated from vehicleTable component in editVehicle method
            infoVehicle: { vehicleID: null, make: '', model:'', type:'', year:null}
        },
        methods:{
            //Called from @save in vehicleInput component, which emits vehicle object,
            //errorMessages object, and status object
            saveVehicle: function(vehicle, errorMessages, status) {
                axios({
                    //If vehicleID is not null, updates record, otherwise creates a new record
                    method: vehicle.vehicleID ? 'put' : 'post',
                    url: 'vehicle-api.php',  //API to send request to
                    data: vehicle //vehicle data from modal component sent in request
                })
                    .then(response => {
                        this.axiosResult = response; //For DEBUG
                        //Set the status to confirm completion
                        status.code = 1;
                        //Clear infoVehicle if result is successful
                        this.infoVehicle= { vehicleID: null, make: '', model:'', type:'', year:null};
                        //Clear the vehicle object in the modal to empty its fields
                        Object.assign(vehicle,this.infoVehicle);
                        //Hide the modal
                        //https://bootstrap-vue.js.org/docs/components/modal
                        this.$bvModal.hide('vehicle-modal');
                        //Refresh the table which will update with any records/changes
                        //emits table refresh event to tableID vehicle-table
                        //https://bootstrap-vue.js.org/docs/components/table#comp-ref-b-table-rootEventListeners
                        this.$root.$emit('bv::refresh::table', 'vehicle-table')
                    })
                    .catch(errors => {
                        let response = errors.response;
                        //Set the status code to indicate no success
                        status.code = 0;
                        //If any errors are returned, populate the modals errors object
                        //to direct field validation
                        Object.assign(errorMessages,response.data);
                    })
            },
            //Called from @edit in vehicleTable, which passes in the record data of the table row
            editVehicle: function(item) {
                //Populate infoVehicle with record data which the modal uses
                //to populate its fields
                Object.assign(this.infoVehicle, item);
                //Reveal the vehicleModal
                //https://bootstrap-vue.js.org/docs/components/modal
                this.$bvModal.show('vehicle-modal');
            },
            //Called from @new in vehicleTable
            newVehicle: function()  {
                //Ensure infoVehicle is empty to keep the modals fields empty
                this.infoVehicle= { vehicleID: null, make: '', model:'', type:'', year:null};
                //Reveal the vehicleModal
                //https://bootstrap-vue.js.org/docs/components/modal
                this.$bvModal.show('vehicle-modal');
            }
        },
        components:{
            //The component for the table
            'vehicle-table': httpVueLoader('./VehicleTable.vue'),
            //The component for the input modal
            'vehicle-input': httpVueLoader('./VehicleInput.vue')
        },
        mounted() {}
    });
</script>
</body>
</html>