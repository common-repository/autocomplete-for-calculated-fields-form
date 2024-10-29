(function () {
    let control_types = ['ftext', 'ftextds'],
		c;

    for (let i in control_types) {

        c = control_types[i];

        if (!('SmartAutocomplete' in fbuilderjQuery.fbuilder.controls[c].prototype)) {
            fbuilderjQuery.fbuilder.controls[c].prototype['SmartAutocomplete'] = false;
        }

        fbuilderjQuery.fbuilder.controls[c].prototype['original_showSpecialDataInstance'] = fbuilderjQuery.fbuilder.controls[c].prototype['showSpecialDataInstance'];

        fbuilderjQuery.fbuilder.controls[c].prototype['showSpecialDataInstance'] = (function (ctrl) {
            return function () {
                let str = '<label><input type="checkbox" name="sSmartAutocomplete" id="sSmartAutocomplete" ' + ((this.SmartAutocomplete) ? "checked" : "") + '>Smart Autocomplete</label>';
                return str + ctrl.prototype.original_showSpecialDataInstance.call(this);
            };
        })(fbuilderjQuery.fbuilder.controls[c]);

        fbuilderjQuery.fbuilder.controls[c].prototype['original_editItemEvents'] = fbuilderjQuery.fbuilder.controls[c].prototype['editItemEvents'];

        fbuilderjQuery.fbuilder.controls[c].prototype['editItemEvents'] = (function (ctrl) {
            return function () {
                fbuilderjQuery('#sSmartAutocomplete').on('change', {
                    obj: this
                }, function (e) {
                    e.data.obj.SmartAutocomplete = fbuilderjQuery(this).is(':checked');
                    fbuilderjQuery.fbuilder.reloadItems({
                        'field': e.data.obj
                    });
                });
                ctrl.prototype.original_editItemEvents.call(this);
            };
        })(fbuilderjQuery.fbuilder.controls[c]);
    }
})();
