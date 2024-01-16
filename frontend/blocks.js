/**
 * External dependencies
 */
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';
import {Content, ariaLabel, Label} from './base';

const settings = getSetting( 'wprs-wc-alipay_data', {} );
const label = ariaLabel({ title: settings.title });

/**
 * Paystack payment method config object.
 */
const Wenprise_Alipay_Gateway = {
  name: 'wprs-wc-alipay',
  label: <Label logoUrls={ settings.logo_urls } title={ label } />,
  content: <Content description={ settings.description } />,
  edit: <Content description={ settings.description } />,
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: settings.supports,
  },
};

registerPaymentMethod( Wenprise_Alipay_Gateway );