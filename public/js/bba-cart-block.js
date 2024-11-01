const { registerPlugin } = wp.plugins;
const { ExperimentalOrderMeta } = wc.blocksCheckout;

const GoodsCheckComponent = ({ cart, extensions }) => {
    return <div className="my-component">Goods Check</div>;
}

const render = () => {
    return (
        <ExperimentalOrderMeta>
            <MyCustomComponent />
        </ExperimentalOrderMeta>
    );
}

registerPlugin('bbamastro', {
    render, 
    scope: 'woocommerce-checkout',
});