<!--
CST226 Jon Ronn
CST230 Anthony Smith
CST232 Matt Smith
An input component (in modal form) for
the Vehicle webpage and database
CWEB280 Assign03
---->

<template>
    <!-- TEMPLATE MUST HAVE A SINGULAR ROOT ELEMENT (they must all be inside 1 tag) -->
    <!-- https://bootstrap-vue.js.org/docs/components/modal/
    header-bg-variant - control the header background variant by setting the background to dark (black background)
    header-text-variant - control the header text variant by setting the text to light (white font)
    footer-bg-variant control the footer background variant by setting the background to dark (black background)
    title - set the title of the modal to display Input Vehicle Information
    id - we set an id so we can call functions like this.bvModal.show() and this.bvModal.hide() in the ui
    no-close-on-backdrop - disable the behavior of closing the modal by clicking outside the modal
    no-close-on-esc - disable the behaviour of closing the modal by clicking the escape character
    @close - when the header close button is pressed "x button in the top right" call the resetFields method which will remove all visible errors from the modal-->
    <b-modal header-bg-variant="dark" header-text-variant="light" footer-bg-variant="dark" title="Input Vehicle Information"
             id="vehicle-modal" no-close-on-backdrop no-close-on-esc @close="resetFields" :hide-header-close="isBusy"  >
                  <!-- This is for the b-form-group make
             :label - Text to place in the label/legend of the form group
             :invalid - Text to show when the form group has an invalid state
             :state - property that controls the validation state of the feedback 'true' force shows valid-feedback, 'false' force shows invalid feedback, 'null' does not force show the feedback

            b-form-input:
            https://bootstrap-vue.js.org/docs/components/form-input
            :disabled - When set to 'true', disables the component's functionality and places it in a disabled state
            v-model - The current value of the input. Result will always be a string, except when the 'number' prop is used
            :state - Controls the validation state appearance of the component. 'true' for valid, 'false' for invalid', or 'null' for no validation state
            @update - event -  Value of input, after any formatting. Not emitted if the value does not change
            trim - When set, trims any leading and trailing white space from the input value. Emulates the Vue '.trim' v-model modifier
             -->
            <b-form-group label="Make:" :invalid-feedback="errors.make" :state="states.make" >
                <b-form-input  :disabled="isBusy" v-model="vehicle.make" :state="states.make" @update="states.make = null" trim></b-form-input>
            </b-form-group>

            <!-- This is for the model -->
            <b-form-group label="Model:" :invalid-feedback="errors.model" :state="states.model">
                <b-form-input  :disabled="isBusy" v-model="vehicle.model" :state="states.model" @update="states.model = null" trim></b-form-input>
            </b-form-group>

             <!-- This is the type group
             https://bootstrap-vue.js.org/docs/components/form-radio/#button-style-radios
             buttons turns the radio buttons into buttons (it looks good)
             button variant - changes the style
             size - Set the size of the component's appearance. 'sm', 'md' (default), or 'lg'
             options - Array of items to render in the component
             -->

            <b-form-group label="Type:" :invalid-feedback="errors.type" :state="states.type">
                <b-form-radio-group class="mx-auto" size="lg" :disabled="isBusy" v-model="vehicle.type" :state="states.type" @update="states.type = null" trim buttons button-variant="primary" :options="['Sedan','Compact', 'Cross Over', 'Truck']"></b-form-radio-group>
            </b-form-group>

            <!-- This is the year group
                type - set to number to change the input field from text to a number with arrows on the right of the input field
                min - minimum value allowed (1898 - 1898 was the first car made ever Therefore, our database can track all automobiles of all time)
                https://americanhistory.si.edu/collections/search/object/nmah_834512
                max - max value allowed (dynamically checks the current

            -->
            <b-form-group  label="Year:" label-cols-sm :invalid-feedback="errors.year" :state="states.year">
                <b-form-input :disabled="isBusy" v-model="vehicle.year" :state="states.year" @update="states.year = null" trim type="number" min="1898" :max="maxYear"></b-form-input>
            </b-form-group>
        <!-- v-slot:modal-footer used to custom render the modal's footer with a 'save' button
        which calls saveVehicle function when clicked
         https://bootstrap-vue.js.org/docs/components/modal/
         v-slot:modal-footer="{save}" - sets a custom button in the modal footer in our case it is save. (save is the key word when calling the modal from the UI)
        size - set size to lg because there is a lot of open space
        variant - Applies one of the Bootstrap theme color variants to the component
        @click.stop - @click is an event (when you click the save button) .stop is a modifier (call this so the click event's propagation will be stopped)
        :disabled - When set to 'true', disables the component's functionality and places it in a disabled state
        <i class="far fa-save"></i> - font awesome text (save button) (i put an id in there so we can css it with some padding to make it look pretty
        https://fontawesome.com/icons/save?style=regular
         -->
        <template v-slot:modal-footer="{save}">
            <!-- https://bootstrap-vue.js.org/docs/components/form-group/
            if you click the save button (status code is -1) waiting to hear back from the api
            display a spinner underneath the header of the modal -->
            <template v-if="status.code === -1">
                <div class="text-center my-2">
                    <b-spinner variant="primary" class="align-middle"></b-spinner>
                </div>
            </template>
            <b-button size="lg" variant="primary" @click.stop="saveVehicle" :disabled="isBusy">Save <i id="save-btn" class="far fa-save"></i></b-button>
        </template>
    </b-modal>
</template>

<script>
    //ecma script modules
    module.exports= {
        props:  {
            //vehicle initially populated from UI infoVehicle
            vehicle: {
                type: Object,
                default: () => ({vehicleID: null, make: '', model: '', type: '', year: null})
            }
        },
        //After vehicle saved, any returned values
        //are populated here for feedback purposes
        data: function()    {
            return {
                newVehicle: Object.assign({}, this.vehicle),
                errors: {},
                status: {code:0}
            }
        },
        methods:    {
            saveVehicle: function() {
                //Send errors object with the emit so that the UI/API can return any validation errors
                //which popup during the validation process
                this.errors = {vehicleID: null, make: null, model: null, type: null, year: null};
                this.status.code = -1;
                //Send the @save call to the UI with the vehicle, errors, and status objects
                this.$emit('save', this.vehicle, this.errors, this.status)
            },
            //resetFields is a helper method that will clear errors if you leave the modal (clicking the 'x' button in the top right)
            //Therefore if you re-open it the errors will be gone and it will be re-populated with the appropriate information for that field
            resetFields: function() {
                this.errors = {vehicleID: null, make: null, model: null, type: null, year: null};
            }
        },
        computed:   {
            //Controls the validation state appearance of the component. 'true' for valid, 'false' for invalid', or 'null' for no validation state
            states: function()  {
                return{
                    make: this.errors.make ? false : null,
                    model: this.errors.model ? false : null,
                    type: this.errors.type ? false : null,
                    year: this.errors.year ? false : null
                };
            },
            //if the status code is anything other than zero isBusy is set to false. When is Busy is true we will usually show a spinner.
            isBusy: function()  {
              return this.status.code !== 0
            },
            maxYear: function() {
                //https://www.w3schools.com/jsref/jsref_getfullyear.asp
                let d = new Date();
                return d.getFullYear() + 1;
            }
        },
        watch:  {
            status: {
                //this watch is keeping track of the status code (if we get a success (status code 1) we can then reset the status code back to 0 (also helps isBusy function)
                deep: true,
                handler: function(newValue, oldValue)   {
                    if(newValue.code===1) {
                        this.status.code = 0;//reset status code after updates have been made
                    }
                }
            }
        }
    }
</script>
<style type="text/css" scoped>
    #save-btn {padding-left: 7px;}
</style>