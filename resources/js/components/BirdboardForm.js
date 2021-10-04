class BirdboardForm {

    constructor(data) {
        this.originalData = JSON.parse(JSON.stringify(data));

        Object.assign(this, data);

        this.errors = {};

        this.submitted = false;
    }

    data() {
        let data = {};

        for (let attributes in this.originalData) {
            data[attributes] = this[attributes];
        }

        return data;
    }

    patch(endpoint) {
        this.submit(endpoint, 'patch');
    }

    delete(endpoint) {
        this.submit(endpoint, 'delete');
    }

    post(endpoint) {
        this.submit(endpoint);
    }

    submit(endpoint, requestType = 'post') {
        return axios[requestType](endpoint, this.data())
            .catch(this.onFail.bind(this))
            .then(this.onSuccess.bind(this));
    }

    onSuccess(response) {
        this.submitted = true;

        this.errors = {};

        return response;
    }

    onFail(errors) {
        this.errors = errors.response.data.errors;

        this.submitted = false;

        throw errors;
    }

    reset() {
        Object.assign(this, this.originalData);
    }

}

export default BirdboardForm;
