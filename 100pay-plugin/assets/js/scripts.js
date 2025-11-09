import { shop100Pay } from 'https://esm.run/@100pay-hq/checkout';

jQuery( function($){
    $('document').ready(onloadActionSet());
});

function onloadActionSet() {
    addPaymentForm(); // add Form to Html Page
    loadPaymentButton(); // Load Payment Side Button
    const submit_btn = $('#_100pay_submit_btn')
    submit_btn.click(validateFormInput())
}


function loadPaymentButton() {
    const button = $('<button class="sticky-side-button">Pay with Crypto</button>');
    button.click(displayPaymentForm())
    $('body').append(button);
};


function displayPaymentForm() {
    $('_100pay-modal-container').css('display', 'block')
};

function addPaymentForm() {
    $('body').append(
        `
        <div id="_100pay-modal-container" style="display: none">
        <div id="_100pay-modal">
            <form id="paymentForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email-address" required />
                </div>
                <div class="form-group">
                    <label for="phone">Phone </label>
                    <input type="tel" id="phone" required />
                </div>
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="number" id="amount" required />
                </div>
                <div class="form-group">
                    <label for="first-name">First Name</label>
                    <input type="text" id="first-name" />
                </div>
                <div class="form-group">
                    <label for="last-name">Last Name</label>
                    <input type="text" id="last-name" />
                </div>
                <div class="form-submit">
                    <button id="_100pay_submit_btn" type="submit">Pay</button>
                </div>
            </form>
        </div>
        `
    );
};

function validateFormInput(event) {
    const email = document.getElementById("email-address").value;
    const phone = document.getElementById("phone").value;
    const amount = document.getElementById("amount").value;
    const firstName = document.getElementById("first-name").value;
    const lastName = document.getElementById("last-name").value;

    if (email != '' && phone != '' && amount != '' && firstName != '' && lastName != '') {
        event.preventDefault();
        payWith100pay()
    } else {
        console.log('Values still missing');
    }
}


function payWith100pay() {
      const email = document.getElementById("email-address").value;
      const phone = document.getElementById("phone").value;
      const amount = document.getElementById("amount").value;
      const firstName = document.getElementById("first-name").value;
      const lastName = document.getElementById("last-name").value;

      console.log(email, phone, amount, firstName, lastName);

      shop100Pay.setup({
      ref_id: "" + Math.floor(Math.random() * 1000000000 + 1),
      api_key: "", // paste api key here
      customer: {
        user_id: "1", // optional
        name: firstName + " " + lastName,
        phone,
        email
      },
      billing: {
        amount,
        currency: "USD", // or any other currency supported by 100pay
        description: "Test Payment",
        country: "USA",
        vat: 10, //optional
        pricing_type: "fixed_price" // or partial
      },
      metadata: {
        is_approved: "yes",
        order_id: "OR2", // optional
        charge_ref: "REF" // optionalm, you can add more fields
      },
      call_back_url: "http://localhost:8000/verifyorder/",
      onClose: msg => {
        alert("You just closed the crypto payment modal.");
      },
      onPayment: reference => {
        alert(`New Payment detected with reference ${reference}`);
        /**
         * @dev ⚠️ never give value to the user because you received a callback.
         * Always verify payments by sending a get request to 100Pay Get Crypto Charge endpoint on your backend.
         * We have written a well detailed article to guide you on how to do this. Check out the link below.
         * 👉 https://100pay.co/blog/how-to-verify-crypto-payments-on-100-pay
         * */
      },
      onError: error => {
        // handle your errors, mostly caused by a broken internet connection.
          console.log(error)
          alert("Sorry something went wrong pls try again.")
      }
    });

}