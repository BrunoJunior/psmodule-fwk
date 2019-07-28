/**
 * Ajouter un message d'erreur
 * @param message
 */
function addBJErrorMessage (message) {
    const errors = $("#bj_alerts .alert-danger");
    errors.append(`${message}<br />`);
    errors.removeClass('hidden');
}

/**
 * @param button
 * @param action
 * @param data
 * @param reactivateOnComplete
 * @return Promise
 */
function callBJAjaxAction(button, action, data, reactivateOnComplete) {
    button.attr('disabled', 'disabled');
    const url = button.data('url');
    const finalData = Object.assign({
        action: 'Action',
        my_action: action,
        ajax: 1
    }, data);
    return new Promise(function (resolve, reject) {
        $.ajax({
            type: "POST",
            url: url,
            dataType: "json",
            data: finalData,
            success: function (retour) {
                if (retour === undefined || retour === null) {
                    addBJErrorMessage('No return !');
                    reject('no return !');
                    return;
                }
                if (retour.error) {
                    const errors = retour.error.split(', ');
                    for (let i=0; i < errors.length; i++) {
                        addBJErrorMessage(errors[i]);
                    }
                }
                if (retour.success) {
                    if (reactivateOnComplete) {
                        button.removeAttr('disabled');
                    }
                    resolve(retour);
                } else {
                    button.removeAttr('disabled');
                    reject(retour);
                }
            },
            error: function (jqXHR, status, error) {
                button.removeAttr('disabled');
                reject({error: error});
            }
        });
    });
}

function sendBJAjaxFile(button, action) {
    button.attr('disabled', 'disabled');
    let formData = new FormData(button.closest('form')[0]);
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: button.data('url') + "&ajax=1&action=Action&my_action=" + action,
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (retour) {
                if (typeof retour === 'string') {
                    retour = JSON.parse(retour);
                }

                if (retour === undefined || retour === null) {
                    addBJErrorMessage('No return !');
                }

                if (retour.error) {
                    const errors = retour.error.split(', ');
                    for (let i=0; i < errors.length; i++) {
                        addBJErrorMessage(errors[i]);
                    }
                }
                if (retour.success) {
                    resolve(retour);
                } else {
                    button.removeAttr('disabled');
                    reject(retour);
                }
            },
            error: function (jqXHR, status, error) {
                button.removeAttr('disabled');
                reject({error: error});
            }
        });
    });
}

/**
 * Gestion barre de progression
 * @param nbSteps
 * @constructor
 */
function BJProgressBar(nbSteps) {
    const that = this;
    this.nbSteps = nbSteps;
    this.step = 0;
    this.progress = $('<div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"><span class="progress-value">0%</span><span class="progress-text"></span></div></div>');
    this.updateNbSteps = function (nbSteps) {
        this.nbSteps = nbSteps;
        const text = this.progress.find('.progress-bar .progress-text').text();
        this.updateValue(Math.round(this.step * 10000 / this.nbSteps) / 100, text);
        return that;
    };
    this.reinit = function (nbSteps, text) {
        this.nbSteps = nbSteps;
        this.step = 0;
        return this.updateValue(0, text);
    };
    this.updateValue = function (value, text) {
        if (text === undefined) {
            text = '';
        } else {
            text = ` (${text})`;
        }
        const progressBar = this.progress.find('.progress-bar');
        progressBar.attr('aria-valuenow', value);
        progressBar.find('.progress-value').text(`${value}%`);
        progressBar.find('.progress-text').text(text);
        progressBar.width(`${value}%`);
        return that;
    };
    this.nextStep = function (text) {
        return this.updateValue(Math.round(++this.step * 10000 / this.nbSteps) / 100, text);
    };
    this.showIn = function (container) {
        container.append(this.progress);
        return that;
    };
    this.hide = function () {
        this.progress.remove();
        return that;
    };
}
