```mermaid
erDiagram
%% =====================================================
%% USER MANAGEMENT & AUTHENTICATION
%% =====================================================

    USERS {
        uuid id PK
        string email UK
        string password_hash
        string first_name
        string last_name
        string phone
        string position
        string avatar_url
        boolean is_active
        timestamptz email_verified_at
        timestamptz last_login_at
        string timezone
        string language
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    ROLES {
        serial id PK
        string name UK
        string description
        jsonb permissions
        timestamptz created_at
        timestamptz updated_at
    }

    USER_ROLES {
        uuid id PK
        uuid user_id FK
        int role_id FK
        uuid assigned_by FK
        timestamptz created_at
    }

    PASSWORD_RESET_TOKENS {
        uuid id PK
        uuid user_id FK
        string token
        timestamptz expires_at
        timestamptz used_at
        timestamptz created_at
    }

    USER_SESSIONS {
        uuid id PK
        uuid user_id FK
        string token_hash
        inet ip_address
        text user_agent
        timestamptz expires_at
        timestamptz last_activity_at
        timestamptz created_at
    }

    %% =====================================================
    %% CRM CORE ENTITIES
    %% =====================================================

    COMPANIES {
        uuid id PK
        string name
        string industry
        string website
        string phone
        string email
        text address_line_1
        text address_line_2
        string city
        string state
        string postal_code
        string country
        text description
        int employees_count
        decimal annual_revenue
        string company_size
        uuid owner_id FK
        string logo_url
        string timezone
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    CONTACTS {
        uuid id PK
        string first_name
        string last_name
        string email
        string phone
        string mobile
        string job_title
        string department
        text address_line_1
        text address_line_2
        string city
        string state
        string postal_code
        string country
        uuid company_id FK
        uuid owner_id FK
        text description
        date date_of_birth
        jsonb social_profiles
        string preferred_contact_method
        boolean is_active
        string lead_source
        timestamptz last_contacted_at
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    TAGS {
        serial id PK
        string name UK
        string color
        text description
        string tag_type
        timestamptz created_at
    }

    CONTACT_TAGS {
        uuid id PK
        uuid contact_id FK
        int tag_id FK
        timestamptz created_at
    }

    %% =====================================================
    %% LEAD MANAGEMENT
    %% =====================================================

    LEAD_SOURCES {
        serial id PK
        string name UK
        text description
        boolean is_active
        timestamptz created_at
    }

    LEAD_STATUSES {
        serial id PK
        string name UK
        text description
        boolean is_active
        boolean is_converted
        int display_order
        string color
        timestamptz created_at
    }

    LEADS {
        uuid id PK
        uuid contact_id FK
        int status_id FK
        int source_id FK
        uuid owner_id FK
        string title
        text description
        decimal estimated_value
        int probability
        date expected_close_date
        int lead_score
        string temperature
        text requirements
        string budget_range
        string decision_timeframe
        text decision_makers
        timestamptz last_activity_at
        timestamptz converted_at
        uuid converted_to_deal_id FK
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    %% =====================================================
    %% DEAL/OPPORTUNITY MANAGEMENT
    %% =====================================================

    DEAL_STAGES {
        serial id PK
        string name UK
        text description
        decimal probability
        int display_order
        boolean is_closed
        boolean is_won
        string color
        timestamptz created_at
    }

    DEAL_TYPES {
        serial id PK
        string name UK
        text description
        boolean is_active
        timestamptz created_at
    }

    DEALS {
        uuid id PK
        string name
        uuid company_id FK
        uuid contact_id FK
        uuid lead_id FK
        uuid owner_id FK
        int stage_id FK
        int type_id FK
        decimal amount
        string currency
        decimal probability
        date expected_close_date
        date actual_close_date
        text description
        text requirements
        text competitors
        text next_steps
        boolean is_won
        text lost_reason
        timestamptz closed_at
        timestamptz last_activity_at
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    %% =====================================================
    %% PRODUCT & SERVICE MANAGEMENT
    %% =====================================================

    SERVICE_CATEGORIES {
        uuid id PK
        string name
        text description
        uuid parent_id FK
        string icon
        string color
        boolean is_active
        int display_order
        timestamptz created_at
        timestamptz updated_at
    }

    PRODUCTS {
        uuid id PK
        string name
        string code UK
        text description
        uuid category_id FK
        decimal unit_price
        decimal cost_price
        string currency
        string service_type
        string billing_cycle
        decimal estimated_hours
        int service_duration_days
        decimal setup_fee
        boolean track_inventory
        int stock_quantity
        int low_stock_threshold
        boolean has_tiered_pricing
        jsonb pricing_tiers
        text[] required_skills
        int complexity_level
        text prerequisites
        boolean is_taxable
        string tax_category
        boolean is_active
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    DEAL_PRODUCTS {
        uuid id PK
        uuid deal_id FK
        uuid product_id FK
        decimal quantity
        decimal unit_price
        decimal discount_percent
        decimal discount_amount
        decimal line_total
        text notes
        timestamptz created_at
        timestamptz updated_at
    }

    %% =====================================================
    %% JOB MANAGEMENT SYSTEM
    %% =====================================================

    JOB_STATUSES {
        serial id PK
        string name UK
        text description
        boolean is_active_status
        boolean is_completed_status
        string color
        int display_order
        timestamptz created_at
    }

    JOB_PRIORITIES {
        serial id PK
        string name UK
        int level UK
        string color
        timestamptz created_at
    }

    JOBS {
        uuid id PK
        string job_number UK
        string title
        text description
        uuid deal_id FK
        uuid customer_company_id FK
        uuid customer_contact_id FK
        uuid created_by FK
        uuid assigned_to FK
        int status_id FK
        int priority_id FK
        date start_date
        date due_date
        decimal estimated_hours
        decimal actual_hours
        int completion_percentage
        text requirements
        text deliverables
        text special_instructions
        text[] required_skills
        decimal estimated_cost
        decimal actual_cost
        decimal billable_amount
        uuid depends_on_job_id FK
        text blocking_reason
        timestamptz started_at
        timestamptz completed_at
        timestamptz approved_at
        uuid approved_by FK
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    JOB_PRODUCTS {
        uuid id PK
        uuid job_id FK
        uuid product_id FK
        decimal quantity
        decimal unit_price
        text notes
        timestamptz created_at
    }

    JOB_ASSIGNMENTS {
        uuid id PK
        uuid job_id FK
        uuid user_id FK
        string role_in_job
        uuid assigned_by FK
        timestamptz assigned_at
        timestamptz removed_at
    }

    %% =====================================================
    %% TASK MANAGEMENT
    %% =====================================================

    TASK_PRIORITIES {
        serial id PK
        string name UK
        int level UK
        string color
        timestamptz created_at
    }

    TASK_STATUSES {
        serial id PK
        string name UK
        boolean is_completed
        boolean is_active
        int display_order
        string color
        timestamptz created_at
    }

    TASKS {
        uuid id PK
        string title
        text description
        uuid assigned_to FK
        uuid created_by FK
        int priority_id FK
        int status_id FK
        uuid contact_id FK
        uuid company_id FK
        uuid lead_id FK
        uuid deal_id FK
        uuid job_id FK
        timestamptz due_date
        int estimated_minutes
        int actual_minutes
        timestamptz completed_at
        int progress_percentage
        text notes
        text completion_notes
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    TASK_REMINDERS {
        uuid id PK
        uuid task_id FK
        timestamptz remind_at
        string reminder_type
        boolean is_sent
        timestamptz sent_at
        timestamptz created_at
    }

    %% =====================================================
    %% TIME TRACKING
    %% =====================================================

    TIME_ENTRIES {
        uuid id PK
        uuid user_id FK
        uuid job_id FK
        uuid task_id FK
        timestamptz start_time
        timestamptz end_time
        int duration_minutes
        text description
        boolean is_billable
        decimal hourly_rate
        decimal billable_amount
        boolean is_approved
        uuid approved_by FK
        timestamptz approved_at
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    %% =====================================================
    %% ACTIVITY & COMMUNICATION TRACKING
    %% =====================================================

    ACTIVITY_TYPES {
        serial id PK
        string name UK
        string icon
        string color
        boolean is_active
        timestamptz created_at
    }

    ACTIVITIES {
        uuid id PK
        int type_id FK
        uuid user_id FK
        uuid contact_id FK
        uuid company_id FK
        uuid lead_id FK
        uuid deal_id FK
        uuid job_id FK
        string subject
        text description
        timestamptz start_time
        timestamptz end_time
        int duration_minutes
        string direction
        string outcome
        text next_action
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    %% =====================================================
    %% DOCUMENT MANAGEMENT
    %% =====================================================

    DOCUMENT_CATEGORIES {
        serial id PK
        string name UK
        text description
        int parent_id FK
        string icon
        boolean is_active
        timestamptz created_at
    }

    DOCUMENTS {
        uuid id PK
        string name
        string original_filename
        text file_path
        int file_size
        string mime_type
        string file_hash
        int category_id FK
        text[] tags
        uuid contact_id FK
        uuid company_id FK
        uuid lead_id FK
        uuid deal_id FK
        uuid job_id FK
        text description
        uuid uploaded_by FK
        boolean is_public
        int version_number
        uuid parent_document_id FK
        jsonb access_permissions
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    %% =====================================================
    %% EXPENSE MANAGEMENT
    %% =====================================================

    EXPENSE_CATEGORIES {
        uuid id PK
        string name UK
        text description
        uuid parent_id FK
        decimal monthly_budget
        decimal yearly_budget
        boolean is_tax_deductible
        string tax_category
        boolean is_active
        boolean requires_approval
        decimal approval_threshold
        timestamptz created_at
        timestamptz updated_at
    }

    EXPENSES {
        uuid id PK
        string expense_number UK
        uuid category_id FK
        decimal amount
        string currency
        date expense_date
        text description
        string reference_number
        string vendor_name
        string vendor_contact
        decimal tax_amount
        boolean is_billable_to_client
        uuid client_company_id FK
        uuid client_contact_id FK
        uuid job_id FK
        decimal markup_percentage
        string payment_method
        string payment_status
        date paid_date
        string status
        uuid submitted_by FK
        uuid approved_by FK
        timestamptz approved_at
        text rejection_reason
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    EXPENSE_ATTACHMENTS {
        uuid id PK
        uuid expense_id FK
        uuid document_id FK
        string attachment_type
        timestamptz created_at
    }

    %% =====================================================
    %% INVOICING SYSTEM
    %% =====================================================

    CUSTOMER_BILLING_INFO {
        uuid id PK
        uuid customer_company_id FK
        uuid customer_contact_id FK
        text billing_address_line_1
        text billing_address_line_2
        string billing_city
        string billing_state
        string billing_postal_code
        string billing_country
        text shipping_address_line_1
        text shipping_address_line_2
        string shipping_city
        string shipping_state
        string shipping_postal_code
        string shipping_country
        int payment_terms_days
        string payment_terms_label
        string preferred_payment_method
        decimal credit_limit
        string tax_id
        string tax_exemption_id
        boolean is_tax_exempt
        decimal default_tax_rate
        string preferred_currency
        uuid price_list_id
        string invoice_delivery_method
        boolean send_payment_reminders
        timestamptz created_at
        timestamptz updated_at
    }

    INVOICE_TEMPLATES {
        uuid id PK
        string name
        text description
        string template_type
        jsonb template_config
        string logo_url
        string company_name
        text company_address
        string company_phone
        string company_email
        string company_website
        boolean is_default
        boolean is_active
        uuid created_by FK
        timestamptz created_at
        timestamptz updated_at
    }

    INVOICES {
        uuid id PK
        string invoice_number UK
        string reference_number
        uuid customer_company_id FK
        uuid customer_contact_id FK
        uuid billing_info_id FK
        uuid deal_id FK
        uuid job_id FK
        uuid quote_id FK
        uuid recurring_invoice_id FK
        date invoice_date
        date due_date
        int payment_terms_days
        string payment_terms_label
        uuid template_id FK
        string currency
        decimal exchange_rate
        decimal subtotal
        string discount_type
        decimal discount_value
        decimal discount_amount
        jsonb tax_details
        decimal total_tax_amount
        boolean is_tax_inclusive
        decimal shipping_charge
        decimal adjustment_amount
        string adjustment_description
        decimal total_amount
        decimal paid_amount
        decimal credits_applied
        decimal balance_due
        decimal stripe_fee_amount
        boolean customer_pays_stripe_fee
        string status
        string approval_status
        text terms_and_conditions
        text public_notes
        text private_notes
        string delivery_method
        boolean email_sent
        timestamptz email_sent_at
        boolean viewed_by_customer
        timestamptz first_viewed_at
        timestamptz last_viewed_at
        date first_payment_date
        date last_payment_date
        int payment_count
        uuid created_by FK
        uuid updated_by FK
        uuid sent_by FK
        uuid approved_by FK
        uuid voided_by FK
        text voided_reason
        timestamptz created_at
        timestamptz updated_at
        timestamptz sent_at
        timestamptz approved_at
        timestamptz voided_at
        timestamptz deleted_at
    }

    INVOICE_LINE_ITEMS {
        uuid id PK
        uuid invoice_id FK
        uuid product_id FK
        string item_type
        string name
        text description
        decimal quantity
        string unit
        decimal unit_price
        string discount_type
        decimal discount_value
        decimal discount_amount
        jsonb tax_details
        decimal tax_amount
        boolean is_tax_inclusive
        decimal line_total
        uuid expense_id FK
        uuid[] time_entry_ids
        string project_code
        text notes
        int sort_order
        timestamptz created_at
        timestamptz updated_at
    }

    %% =====================================================
    %% QUOTES/ESTIMATES
    %% =====================================================

    QUOTE_STATUSES {
        serial id PK
        string name UK
        text description
        boolean is_active
        boolean is_approved
        boolean is_rejected
        string color
        timestamptz created_at
    }

    QUOTES {
        uuid id PK
        string quote_number UK
        string title
        uuid customer_company_id FK
        uuid customer_contact_id FK
        uuid deal_id FK
        uuid job_id FK
        date quote_date
        date valid_until
        date expiry_date
        uuid template_id FK
        string currency
        decimal subtotal
        decimal discount_amount
        decimal tax_amount
        decimal total_amount
        int status_id FK
        text terms_and_conditions
        text scope_of_work
        text assumptions
        text exclusions
        text payment_schedule
        text public_notes
        text private_notes
        boolean sent_to_customer
        boolean viewed_by_customer
        jsonb customer_signature
        timestamptz signed_at
        string signed_by_name
        string signed_by_email
        boolean converted_to_invoice
        uuid converted_invoice_id FK
        timestamptz converted_at
        int version_number
        uuid parent_quote_id FK
        text revision_reason
        uuid created_by FK
        uuid updated_by FK
        uuid sent_by FK
        timestamptz created_at
        timestamptz updated_at
        timestamptz sent_at
        timestamptz deleted_at
    }

    QUOTE_LINE_ITEMS {
        uuid id PK
        uuid quote_id FK
        uuid product_id FK
        string item_type
        string name
        text description
        decimal quantity
        string unit
        decimal unit_price
        decimal discount_amount
        decimal tax_amount
        decimal line_total
        boolean is_optional
        uuid alternative_to_line_id FK
        text notes
        int sort_order
        timestamptz created_at
    }

    %% =====================================================
    %% PAYMENT PROCESSING
    %% =====================================================

    PAYMENT_METHODS {
        serial id PK
        string name UK
        string code UK
        text description
        boolean is_online
        boolean requires_reference
        decimal processing_fee_percentage
        decimal processing_fee_fixed
        boolean is_active
        int sort_order
        timestamptz created_at
    }

    PAYMENTS {
        uuid id PK
        string payment_number UK
        uuid invoice_id FK
        decimal amount
        string currency
        date payment_date
        int payment_method_id FK
        string reference_number
        string stripe_payment_intent_id
        string stripe_charge_id
        decimal stripe_fee_amount
        decimal processing_fee
        decimal net_amount
        string status
        text failure_reason
        string card_last_four
        string card_brand
        string bank_name
        text notes
        string receipt_url
        boolean receipt_email_sent
        uuid recorded_by FK
        timestamptz processed_at
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    PAYMENT_LINKS {
        uuid id PK
        uuid invoice_id FK
        string link_token UK
        decimal amount
        string currency
        text description
        string stripe_payment_intent_id
        boolean include_stripe_fee
        timestamptz expires_at
        int max_usage_count
        int current_usage_count
        boolean is_active
        boolean is_used
        string qr_code_url
        uuid created_by FK
        timestamptz first_accessed_at
        timestamptz last_accessed_at
        int access_count
        timestamptz created_at
        timestamptz updated_at
    }

    %% =====================================================
    %% REFUNDS & CREDITS
    %% =====================================================

    REFUND_REASONS {
        serial id PK
        string name UK
        text description
        boolean requires_approval
        boolean is_active
        timestamptz created_at
    }

    REFUNDS {
        uuid id PK
        string refund_number UK
        uuid payment_id FK
        uuid invoice_id FK
        decimal amount
        string currency
        int reason_id FK
        text reason_description
        date refund_date
        string stripe_refund_id
        string stripe_status
        decimal processing_fee
        decimal net_refund_amount
        string status
        uuid approved_by FK
        timestamptz approved_at
        timestamptz processed_at
        text failure_reason
        text notes
        boolean customer_notification_sent
        uuid requested_by FK
        uuid processed_by FK
        timestamptz created_at
        timestamptz updated_at
    }

    CREDIT_NOTES {
        uuid id PK
        string credit_number UK
        uuid customer_company_id FK
        uuid customer_contact_id FK
        decimal amount
        string currency
        string reason
        text description
        date credit_date
        date expires_at
        decimal balance_remaining
        boolean is_fully_used
        uuid invoice_id FK
        uuid refund_id FK
        string status
        uuid created_by FK
        timestamptz created_at
        timestamptz updated_at
    }

    CREDIT_APPLICATIONS {
        uuid id PK
        uuid credit_note_id FK
        uuid invoice_id FK
        decimal amount_applied
        date applied_date
        uuid applied_by FK
        text notes
        timestamptz created_at
    }

    %% =====================================================
    %% RECURRING INVOICES
    %% =====================================================

    RECURRING_FREQUENCIES {
        serial id PK
        string name UK
        string interval_type
        int interval_value
        text description
        boolean is_active
        timestamptz created_at
    }

    RECURRING_INVOICES {
        uuid id PK
        string recurring_name
        uuid customer_company_id FK
        uuid customer_contact_id FK
        uuid template_invoice_id FK
        uuid billing_info_id FK
        uuid template_id FK
        int frequency_id FK
        int interval_count
        date start_date
        date end_date
        date next_invoice_date
        date last_invoice_date
        int max_invoices
        int invoices_generated
        string currency
        decimal subtotal
        decimal tax_amount
        decimal total_amount
        boolean is_active
        boolean is_paused
        text paused_reason
        date paused_until
        text description
        text notes
        uuid created_by FK
        uuid updated_by FK
        timestamptz created_at
        timestamptz updated_at
        timestamptz deleted_at
    }

    RECURRING_INVOICE_LINE_ITEMS {
        uuid id PK
        uuid recurring_invoice_id FK
        uuid product_id FK
        string item_type
        string name
        text description
        decimal quantity
        string unit
        decimal unit_price
        decimal tax_rate
        decimal line_total
        int sort_order
        timestamptz created_at
    }

    RECURRING_INVOICE_HISTORY {
        uuid id PK
        uuid recurring_invoice_id FK
        uuid generated_invoice_id FK
        date generation_date
        date billing_period_start
        date billing_period_end
        decimal amount
        string status
        text notes
        timestamptz created_at
    }

    %% =====================================================
    %% EMAIL & COMMUNICATION TRACKING
    %% =====================================================

    EMAIL_TEMPLATES {
        uuid id PK
        string name
        string template_type
        string subject
        text body_html
        text body_text
        jsonb available_variables
        boolean is_default
        boolean is_active
        uuid created_by FK
        uuid updated_by FK
        timestamptz created_at
        timestamptz updated_at
    }

    EMAIL_LOG {
        uuid id PK
        string to_email
        string to_name
        string from_email
        string from_name
        string subject
        text body_html
        text body_text
        uuid template_id FK
        uuid invoice_id FK
        uuid quote_id FK
        uuid payment_id FK
        uuid contact_id FK
        string email_provider
        string provider_message_id
        string status
        timestamptz sent_at
        timestamptz delivered_at
        timestamptz opened_at
        timestamptz clicked_at
        timestamptz bounced_at
        text bounce_reason
        boolean has_attachments
        int attachment_count
        uuid sent_by FK
        timestamptz created_at
        timestamptz updated_at
    }

    PAYMENT_REMINDERS {
        uuid id PK
        uuid invoice_id FK
        string reminder_type
        int days_offset
        uuid email_template_id FK
        string subject
        text message
        boolean is_sent
        timestamptz scheduled_for
        timestamptz sent_at
        uuid email_log_id FK
        boolean is_active
        uuid created_by FK
        timestamptz created_at
    }

    %% =====================================================
    %% CUSTOMER PORTAL
    %% =====================================================

    CUSTOMER_PORTAL_ACCESS {
        uuid id PK
        uuid customer_company_id FK
        uuid customer_contact_id FK
        string email
        string password_hash
        string access_token UK
        timestamptz token_expires_at
        boolean is_active
        boolean email_verified
        string email_verification_token
        timestamptz last_login_at
        int login_count
        boolean can_view_invoices
        boolean can_make_payments
        boolean can_view_quotes
        boolean can_download_documents
        uuid created_by FK
        timestamptz created_at
        timestamptz updated_at
    }

    CUSTOMER_PORTAL_ACTIVITY {
        uuid id PK
        uuid portal_access_id FK
        string activity_type
        uuid invoice_id FK
        uuid quote_id FK
        uuid payment_id FK
        uuid document_id FK
        text description
        inet ip_address
        text user_agent
        timestamptz created_at
    }

    %% =====================================================
    %% SYSTEM AUDIT & LOGGING
    %% =====================================================

    AUDIT_LOGS {
        uuid id PK
        string table_name
        uuid record_id
        string action
        jsonb old_values
        jsonb new_values
        text[] changed_fields
        uuid user_id FK
        string user_email
        string user_role
        inet ip_address
        text user_agent
        string session_id
        uuid request_id
        string operation_type
        timestamptz created_at
    }

    SYSTEM_SETTINGS {
        uuid id PK
        string setting_key UK
        jsonb setting_value
        text description
        string setting_type
        boolean is_encrypted
        boolean is_public
        jsonb validation_rules
        uuid updated_by FK
        timestamptz created_at
        timestamptz updated_at
    }

    WEBHOOK_ENDPOINTS {
        uuid id PK
        string name
        text url
        string secret_key
        text[] events
        boolean is_active
        int retry_attempts
        int timeout_seconds
        jsonb custom_headers
        timestamptz last_success_at
        timestamptz last_failure_at
        int failure_count
        uuid created_by FK
        timestamptz created_at
        timestamptz updated_at
    }

    WEBHOOK_DELIVERIES {
        uuid id PK
        uuid webhook_endpoint_id FK
        string event_type
        jsonb payload
        string related_table
        uuid related_id
        int http_status
        text response_body
        jsonb response_headers
        int delivery_duration_ms
        string status
        int attempt_count
        timestamptz next_retry_at
        text error_message
        timestamptz created_at
        timestamptz delivered_at
    }

    %% =====================================================
    %% RELATIONSHIPS
    %% =====================================================

    %% User Management Relationships
    USERS ||--o{ USER_ROLES : has
    USER_ROLES }o--|| ROLES : belongs_to
    USERS ||--o{ PASSWORD_RESET_TOKENS : requests
    USERS ||--o{ USER_SESSIONS : has

    %% CRM Core Relationships
    USERS ||--o{ COMPANIES : owns
    COMPANIES ||--o{ CONTACTS : has
    USERS ||--o{ CONTACTS : owns
    CONTACTS ||--o{ CONTACT_TAGS : has
    CONTACT_TAGS }o--|| TAGS : references

    %% Lead Management Relationships
    CONTACTS ||--o{ LEADS : becomes
    LEADS }o--|| LEAD_SOURCES : comes_from
    LEADS }o--|| LEAD_STATUSES : has
    USERS ||--o{ LEADS : owns
    LEADS ||--o{ DEALS : converts_to

    %% Deal Management Relationships
    COMPANIES ||--o{ DEALS : associated_with
    CONTACTS ||--o{ DEALS : associated_with
    LEADS ||--o{ DEALS : converts_to
    USERS ||--o{ DEALS : owns
    DEALS }o--|| DEAL_STAGES : belongs_to
    DEALS }o--|| DEAL_TYPES : categorized_as
    DEALS ||--o{ DEAL_PRODUCTS : includes
    DEAL_PRODUCTS }o--|| PRODUCTS : references

    %% Product & Service Relationships
    SERVICE_CATEGORIES ||--o{ SERVICE_CATEGORIES : has_subcategory
    SERVICE_CATEGORIES ||--o{ PRODUCTS : categorizes
    PRODUCTS ||--o{ DEAL_PRODUCTS : used_in
    PRODUCTS ||--o{ JOB_PRODUCTS : used_in

    %% Job Management Relationships
    DEALS ||--o{ JOBS : creates
    COMPANIES ||--o{ JOBS : receives_service
    CONTACTS ||--o{ JOBS : receives_service
    USERS ||--o{ JOBS : creates
    USERS ||--o{ JOBS : assigned_to
    JOBS }o--|| JOB_STATUSES : has
    JOBS }o--|| JOB_PRIORITIES : has
    JOBS ||--o{ JOBS : depends_on
    JOBS ||--o{ JOB_PRODUCTS : includes
    JOB_PRODUCTS }o--|| PRODUCTS : references
    JOBS ||--o{ JOB_ASSIGNMENTS : has
    JOB_ASSIGNMENTS }o--|| USERS : assigned_to
    USERS ||--o{ JOB_ASSIGNMENTS : assigns

    %% Task Management Relationships
    USERS ||--o{ TASKS : assigned_to
    USERS ||--o{ TASKS : creates
    TASKS }o--|| TASK_PRIORITIES : has
    TASKS }o--|| TASK_STATUSES : has
    CONTACTS ||--o{ TASKS : related_to
    COMPANIES ||--o{ TASKS : related_to
    LEADS ||--o{ TASKS : related_to
    DEALS ||--o{ TASKS : related_to
    JOBS ||--o{ TASKS : related_to
    TASKS ||--o{ TASK_REMINDERS : has

    %% Time Tracking Relationships
    USERS ||--o{ TIME_ENTRIES : logs
    JOBS ||--o{ TIME_ENTRIES : tracks
    TASKS ||--o{ TIME_ENTRIES : tracks
    USERS ||--o{ TIME_ENTRIES : approves

    %% Activity Tracking Relationships
    USERS ||--o{ ACTIVITIES : performs
    ACTIVITIES }o--|| ACTIVITY_TYPES : categorized_as
    CONTACTS ||--o{ ACTIVITIES : related_to
    COMPANIES ||--o{ ACTIVITIES : related_to
    LEADS ||--o{ ACTIVITIES : related_to
    DEALS ||--o{ ACTIVITIES : related_to
    JOBS ||--o{ ACTIVITIES : related_to

    %% Document Management Relationships
    DOCUMENT_CATEGORIES ||--o{ DOCUMENT_CATEGORIES : has_subcategory
    DOCUMENTS }o--|| DOCUMENT_CATEGORIES : categorized_by
    USERS ||--o{ DOCUMENTS : uploads
    CONTACTS ||--o{ DOCUMENTS : attached_to
    COMPANIES ||--o{ DOCUMENTS : attached_to
    LEADS ||--o{ DOCUMENTS : attached_to
    DEALS ||--o{ DOCUMENTS : attached_to
    JOBS ||--o{ DOCUMENTS : attached_to
    DOCUMENTS ||--o{ DOCUMENTS : has_version

    %% Expense Management Relationships
    EXPENSE_CATEGORIES ||--o{ EXPENSE_CATEGORIES : has_subcategory
    EXPENSES }o--|| EXPENSE_CATEGORIES : categorized_by
    USERS ||--o{ EXPENSES : submits
    USERS ||--o{ EXPENSES : approves
    COMPANIES ||--o{ EXPENSES : billable_to
    CONTACTS ||--o{ EXPENSES : billable_to
    JOBS ||--o{ EXPENSES : related_to
    EXPENSES ||--o{ EXPENSE_ATTACHMENTS : has
    EXPENSE_ATTACHMENTS }o--|| DOCUMENTS : references

    %% Invoicing System Relationships
    COMPANIES ||--o{ CUSTOMER_BILLING_INFO : has
    CONTACTS ||--o{ CUSTOMER_BILLING_INFO : has
    USERS ||--o{ INVOICE_TEMPLATES : creates
    COMPANIES ||--o{ INVOICES : receives
    CONTACTS ||--o{ INVOICES : receives
    CUSTOMER_BILLING_INFO ||--o{ INVOICES : used_for
    DEALS ||--o{ INVOICES : generates
    JOBS ||--o{ INVOICES : generates
    QUOTES ||--o{ INVOICES : converts_to
    RECURRING_INVOICES ||--o{ INVOICES : generates
    INVOICE_TEMPLATES ||--o{ INVOICES : uses
    USERS ||--o{ INVOICES : creates
    USERS ||--o{ INVOICES : updates
    USERS ||--o{ INVOICES : sends
    USERS ||--o{ INVOICES : approves
    USERS ||--o{ INVOICES : voids
    INVOICES ||--o{ INVOICE_LINE_ITEMS : contains
    INVOICE_LINE_ITEMS }o--|| PRODUCTS : references
    INVOICE_LINE_ITEMS }o--|| EXPENSES : bills

    %% Quote Management Relationships
    COMPANIES ||--o{ QUOTES : receives
    CONTACTS ||--o{ QUOTES : receives
    DEALS ||--o{ QUOTES : generates
    JOBS ||--o{ QUOTES : generates
    INVOICE_TEMPLATES ||--o{ QUOTES : uses
    QUOTES }o--|| QUOTE_STATUSES : has
    USERS ||--o{ QUOTES : creates
    USERS ||--o{ QUOTES : updates
    USERS ||--o{ QUOTES : sends
    QUOTES ||--o{ QUOTES : has_revision
    QUOTES ||--o{ QUOTE_LINE_ITEMS : contains
    QUOTE_LINE_ITEMS }o--|| PRODUCTS : references
    QUOTE_LINE_ITEMS ||--o{ QUOTE_LINE_ITEMS : alternative_to

    %% Payment Processing Relationships
    INVOICES ||--o{ PAYMENTS : receives
    PAYMENTS }o--|| PAYMENT_METHODS : uses
    USERS ||--o{ PAYMENTS : records
    INVOICES ||--o{ PAYMENT_LINKS : generates
    USERS ||--o{ PAYMENT_LINKS : creates

    %% Refunds & Credits Relationships
    PAYMENTS ||--o{ REFUNDS : generates
    INVOICES ||--o{ REFUNDS : affects
    REFUNDS }o--|| REFUND_REASONS : has
    USERS ||--o{ REFUNDS : requests
    USERS ||--o{ REFUNDS : approves
    USERS ||--o{ REFUNDS : processes
    COMPANIES ||--o{ CREDIT_NOTES : receives
    CONTACTS ||--o{ CREDIT_NOTES : receives
    INVOICES ||--o{ CREDIT_NOTES : generates
    REFUNDS ||--o{ CREDIT_NOTES : creates
    USERS ||--o{ CREDIT_NOTES : creates
    CREDIT_NOTES ||--o{ CREDIT_APPLICATIONS : applied_as
    CREDIT_APPLICATIONS }o--|| INVOICES : applied_to
    USERS ||--o{ CREDIT_APPLICATIONS : applies

    %% Recurring Invoices Relationships
    COMPANIES ||--o{ RECURRING_INVOICES : receives
    CONTACTS ||--o{ RECURRING_INVOICES : receives
    INVOICES ||--o{ RECURRING_INVOICES : templates
    CUSTOMER_BILLING_INFO ||--o{ RECURRING_INVOICES : uses
    INVOICE_TEMPLATES ||--o{ RECURRING_INVOICES : uses
    RECURRING_FREQUENCIES ||--o{ RECURRING_INVOICES : defines
    USERS ||--o{ RECURRING_INVOICES : creates
    USERS ||--o{ RECURRING_INVOICES : updates
    RECURRING_INVOICES ||--o{ RECURRING_INVOICE_LINE_ITEMS : contains
    RECURRING_INVOICE_LINE_ITEMS }o--|| PRODUCTS : references
    RECURRING_INVOICES ||--o{ RECURRING_INVOICE_HISTORY : tracks
    RECURRING_INVOICE_HISTORY }o--|| INVOICES : generated

    %% Email & Communication Relationships
    USERS ||--o{ EMAIL_TEMPLATES : creates
    USERS ||--o{ EMAIL_TEMPLATES : updates
    EMAIL_TEMPLATES ||--o{ EMAIL_LOG : uses
    INVOICES ||--o{ EMAIL_LOG : generates
    QUOTES ||--o{ EMAIL_LOG : generates
    PAYMENTS ||--o{ EMAIL_LOG : generates
    CONTACTS ||--o{ EMAIL_LOG : receives
    USERS ||--o{ EMAIL_LOG : sends
    INVOICES ||--o{ PAYMENT_REMINDERS : schedules
    EMAIL_TEMPLATES ||--o{ PAYMENT_REMINDERS : uses
    USERS ||--o{ PAYMENT_REMINDERS : creates
    PAYMENT_REMINDERS }o--|| EMAIL_LOG : results_in

    %% Customer Portal Relationships
    COMPANIES ||--o{ CUSTOMER_PORTAL_ACCESS : has
    CONTACTS ||--o{ CUSTOMER_PORTAL_ACCESS : has
    USERS ||--o{ CUSTOMER_PORTAL_ACCESS : creates
    CUSTOMER_PORTAL_ACCESS ||--o{ CUSTOMER_PORTAL_ACTIVITY : logs
    INVOICES ||--o{ CUSTOMER_PORTAL_ACTIVITY : viewed_in
    QUOTES ||--o{ CUSTOMER_PORTAL_ACTIVITY : viewed_in
    PAYMENTS ||--o{ CUSTOMER_PORTAL_ACTIVITY : made_in
    DOCUMENTS ||--o{ CUSTOMER_PORTAL_ACTIVITY : accessed_in

    %% System Administration Relationships
    USERS ||--o{ AUDIT_LOGS : generates
    USERS ||--o{ SYSTEM_SETTINGS : updates
    USERS ||--o{ WEBHOOK_ENDPOINTS : creates
    WEBHOOK_ENDPOINTS ||--o{ WEBHOOK_DELIVERIES : sends
```
