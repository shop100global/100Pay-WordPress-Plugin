const { registerPaymentMethod } = window.wc.wcBlocksRegistry
const { getSetting } = window.wc.wcSettings

const settings = getSetting( 'pay100_data', {} )

const label = settings.title

const Content = () => {
	// return decodeEntities( settings.description || '' )
	return 'Pay for Products using our Crypto Payment Gateway' 
}

const { createElement : E } = React



const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components
	return E(PaymentMethodLabel , {text: label } )
}

registerPaymentMethod( {
	name: "pay100",
	label: E(Label),
	content: E(Content),
	edit: E(Content),
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		features: settings.supports,
	}
} )




// const pay100_settings = window.wc.wcSettings.getSetting( 'pay100_data', {} )

// const pay100_label = pay100_settings.title

// const Pay100Content = () => {
// 	return pay100_settings.description || 'Default Text'  
// }

// const Pay100Label = ( props ) => {
// 	const { PaymentMethodLabel } = props.components
// 	return React.createElement(PaymentMethodLabel , {text: pay100_label } )
// }

// window.wc.wcBlocksRegistry.registerPaymentMethod( {
// 	name: "pay100",
// 	label: React.createElement(Pay100Label),
// 	content: React.createElement(Pay100Content),
// 	edit: React.createElement(Pay100Content),
// 	canMakePayment: () => true,
// 	ariaLabel: pay100_label,
// 	supports: {
// 		features: pay100_settings.supports,
// 	}
// } )

