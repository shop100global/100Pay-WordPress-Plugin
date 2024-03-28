//import { decodeEntities } from '@wordpress/html-entities';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry
const { getSetting } = window.wc.wcSettings

const settings = getSetting( '100pay_data', {} )

//const label = decodeEntities( settings.title )
const label = settings.title

const Content = () => {
	//return decodeEntities( settings.description || '' )
	return '100Pay Crypto Payment Gateway' 
}

const { createElement : E } = React



const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components
	return E(PaymentMethodLabel , {text: label } )
}

registerPaymentMethod( {
	name: "100Pay",
	label: E(Label),
	content: E(Content),
	edit: E(Content),
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		features: settings.supports,
	}
} )

