const { registerPaymentMethod } = window.wc.wcBlocksRegistry
const { getSetting } = window.wc.wcSettings

const settings = getSetting( 'pay100_data', {} )

const label = settings.title

const Content = () => {
	
	return settings.description
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

