import axios from './axios';
import Errors from './Errors';

class Form {
    constructor(data) {
        this.originalData = data;

        for (let field in data) {
            this[field] = data[field];
        }

        this.errors = new Errors();
    }

    data() {
        let data = {};

        for (let property in this.originalData) {
            data[property] = this[property];
        }

        return data;
    }

    reset() {
        for (let field in this.originalData) {
            this[field] = '';
        }

        this.errors.clear();
    }

    post(url, data = null) {
        return this.submit('post', url, data);
    }

    submit(requestType, url, data = null) {
        let formData = data || this.data();

        return new Promise((resolve, reject) => {
            axios[requestType](url, formData)
                .then(response => {
                    this.onSuccess(response.data);

                    resolve(response.data);
                })
                .catch(error => {
                    if (error.response.status == 422) {
                        this.onFail(error.response.data.errors);
                    } else {
                        this.onFail(error.response.data);
                    }

                    reject(error.response.data);
                });
        });
    }

    onSuccess(data) {
        this.reset();
    }

    onFail(errors) {
        this.errors.record(errors);
    }
}

export default Form;
