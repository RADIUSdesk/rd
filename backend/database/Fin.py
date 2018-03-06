from sqlalchemy import BigInteger, Column, Date, DateTime, Float, Index, Integer, Numeric, String, Text, text
from sqlalchemy.dialects.mysql.enumerated import ENUM
from . import Base


class FinAuthorizeNetTransactionNote(Base):
    __tablename__ = 'fin_authorize_net_transaction_notes'

    id = Column(Integer, primary_key=True)
    fin_authorize_net_transaction_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinAuthorizeNetTransaction(Base):
    __tablename__ = 'fin_authorize_net_transactions'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    voucher_id = Column(Integer)
    voucher_name = Column(
        String(50), nullable=False, server_default=text("''"))
    top_up_id = Column(Integer)
    description = Column(String(50), nullable=False, server_default=text("''"))
    x_response_code = Column(Integer)
    x_response_subcode = Column(Integer)
    x_response_reason_code = Column(Integer)
    x_response_reason_text = Column(
        String(200), nullable=False, server_default=text("''"))
    x_auth_code = Column(String(50), nullable=False, server_default=text("''"))
    x_avs_code = Column(String(50), nullable=False, server_default=text("''"))
    x_trans_id = Column(String(50), nullable=False, server_default=text("''"))
    x_method = Column(String(5), nullable=False, server_default=text("''"))
    x_card_type = Column(String(50), nullable=False, server_default=text("''"))
    x_account_number = Column(
        String(50), nullable=False, server_default=text("''"))
    x_first_name = Column(
        String(50), nullable=False, server_default=text("''"))
    x_last_name = Column(String(50), nullable=False, server_default=text("''"))
    x_company = Column(String(50), nullable=False, server_default=text("''"))
    x_address = Column(String(50), nullable=False, server_default=text("''"))
    x_city = Column(String(50), nullable=False, server_default=text("''"))
    x_state = Column(String(50), nullable=False, server_default=text("''"))
    x_zip = Column(String(50), nullable=False, server_default=text("''"))
    x_country = Column(String(50), nullable=False, server_default=text("''"))
    x_phone = Column(String(50), nullable=False, server_default=text("''"))
    x_fax = Column(String(50), nullable=False, server_default=text("''"))
    x_email = Column(String(50), nullable=False, server_default=text("''"))
    x_amount = Column(Numeric(10, 2), nullable=False)
    x_catalog_link_id = Column(
        String(50), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
    tag = Column(String(100), nullable=False, server_default=text("'unknown'"))


class FinMyGateTokenFailure(Base):
    __tablename__ = 'fin_my_gate_token_failures'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    permanent_user_id = Column(Integer)
    fin_payment_plan_id = Column(Integer)
    error_code = Column(String(255), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinMyGateTokenNote(Base):
    __tablename__ = 'fin_my_gate_token_notes'

    id = Column(Integer, primary_key=True)
    fin_my_gate_token_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinMyGateToken(Base):
    __tablename__ = 'fin_my_gate_tokens'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    permanent_user_id = Column(Integer)
    fin_payment_plan_id = Column(Integer)
    client_pin = Column(String(50), nullable=False)
    client_uci = Column(String(50), nullable=False)
    client_uid = Column(String(50), nullable=False)
    override = Column(Numeric(15, 2))
    override_completed = Column(
        Integer, nullable=False, server_default=text("0"))
    active = Column(Integer, nullable=False, server_default=text("0"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinMyGateTransactionNote(Base):
    __tablename__ = 'fin_my_gate_transaction_notes'

    id = Column(Integer, primary_key=True)
    fin_my_gate_transaction_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinMyGateTransaction(Base):
    __tablename__ = 'fin_my_gate_transactions'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    fin_my_gate_token_id = Column(Integer)
    status = Column(
        ENUM('pending', 'success', 'fail', 'submitted'),
        server_default=text("'pending'"))
    type = Column(
        ENUM('credit_card', 'debit_order'),
        server_default=text("'credit_card'"))
    amount = Column(Numeric(15, 2))
    my_gate_reference = Column(
        String(255), nullable=False, server_default=text("''"))
    message = Column(String(255), nullable=False, server_default=text("''"))
    permanent_user = Column(
        String(255), nullable=False, server_default=text("''"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinPayUTransactionNote(Base):
    __tablename__ = 'fin_pay_u_transaction_notes'

    id = Column(Integer, primary_key=True)
    fin_pay_u_transaction_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinPayUTransaction(Base):
    __tablename__ = 'fin_pay_u_transactions'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    voucher_id = Column(Integer)
    top_up_id = Column(Integer)
    merchantReference = Column(String(64), nullable=False)
    payUReference = Column(String(64), nullable=False)
    TransactionType = Column(
        ENUM('RESERVE', 'FINALISE', 'PAYMENT', 'EFFECT_STAGING', 'CREDIT',
             'RESERVE_CANCEL', 'REGISTER_LINK'),
        server_default=text("'PAYMENT'"))
    TransactionState = Column(
        ENUM('NEW', 'PROCESSING', 'SUCCESSFUL', 'FAILED', 'TIMEOUT'),
        server_default=text("'NEW'"))
    ResultCode = Column(Integer)
    ResultMessage = Column(String(255))
    DisplayMessage = Column(String(255))
    merchUserId = Column(String(255))
    firstName = Column(String(255))
    lastName = Column(String(255))
    email = Column(String(255))
    mobile = Column(String(255))
    regionalId = Column(String(255))
    amountInCents = Column(Integer, nullable=False)
    currencyCode = Column(String(255), server_default=text("'ZAR'"))
    description = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinPaymentPlanNote(Base):
    __tablename__ = 'fin_payment_plan_notes'

    id = Column(Integer, primary_key=True)
    fin_payment_plan_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinPaymentPlan(Base):
    __tablename__ = 'fin_payment_plans'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    profile_id = Column(Integer)
    name = Column(String(255), nullable=False)
    description = Column(String(255), server_default=text("''"))
    type = Column(ENUM('voucher', 'user'), server_default=text("'user'"))
    currency_code = Column(
        ENUM('USD', 'ZAR', 'GBP', 'EUR'), server_default=text("'ZAR'"))
    value = Column(Numeric(15, 2))
    tax = Column(Numeric(15, 2))
    active = Column(Integer, nullable=False, server_default=text("0"))
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinPaypalTransactionNote(Base):
    __tablename__ = 'fin_paypal_transaction_notes'

    id = Column(Integer, primary_key=True)
    fin_paypal_transaction_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinPaypalTransaction(Base):
    __tablename__ = 'fin_paypal_transactions'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    voucher_id = Column(Integer)
    top_up_id = Column(Integer)
    business = Column(String(255), nullable=False)
    txn_id = Column(String(20), nullable=False)
    option_name1 = Column(String(255))
    option_selection1 = Column(String(255))
    item_name = Column(String(255))
    item_number = Column(String(255))
    first_name = Column(String(255))
    last_name = Column(String(255))
    payer_email = Column(String(255))
    payer_id = Column(String(255))
    payer_status = Column(String(255))
    payment_gross = Column(Numeric(10, 2), nullable=False)
    mc_gross = Column(Numeric(10, 2), nullable=False)
    mc_fee = Column(Numeric(10, 2), nullable=False)
    mc_currency = Column(String(255), server_default=text("'GBP'"))
    payment_date = Column(String(255), nullable=False)
    payment_status = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinPayuTransaction(Base):
    __tablename__ = 'fin_payu_transactions'

    id = Column(Integer, primary_key=True)
    merchant_reference = Column(String(64), nullable=False)
    payu_reference = Column(String(64), nullable=False)
    transaction_type = Column(
        ENUM('RESERVE', 'FINALISE', 'PAYMENT', 'EFFECT_STAGING', 'CREDIT',
             'RESERVE_CANCEL', 'REGISTER_LINK'),
        server_default=text("'PAYMENT'"))
    transaction_state = Column(
        ENUM('NEW', 'PROCESSING', 'SUCCESSFUL', 'FAILED'),
        server_default=text("'NEW'"))
    result_code = Column(Integer)
    result_message = Column(String(255))
    display_message = Column(String(255))
    merchant_user_id = Column(String(255))
    email = Column(String(255))
    mobile = Column(String(255))
    regional_id = Column(String(255))
    first_name = Column(String(255))
    last_name = Column(String(255))
    amount_in_cents = Column(Integer, nullable=False)
    currency_code = Column(String(255), server_default=text("'ZAR'"))
    description = Column(String(255), nullable=False)
    product_code = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinPremiumSmsTransactionNote(Base):
    __tablename__ = 'fin_premium_sms_transaction_notes'

    id = Column(Integer, primary_key=True)
    fin_premium_sms_transaction_id = Column(Integer, nullable=False)
    note_id = Column(Integer, nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)


class FinPremiumSmsTransaction(Base):
    __tablename__ = 'fin_premium_sms_transactions'

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    voucher_id = Column(Integer)
    top_up_id = Column(Integer)
    mobile = Column(String(255))
    description = Column(String(255), nullable=False)
    created = Column(DateTime, nullable=False)
    modified = Column(DateTime, nullable=False)
