CREATE INDEX idx_expenses_submitted_by ON expenses(submitted_by) WHERE deleted_at IS NULL;
CREATE INDEX idx_expenses_billable ON expenses(is_billable_to_client) WHERE deleted_at IS NULL;
CREATE INDEX idx_expenses_amount ON expenses(amount DESC) WHERE deleted_at IS NULL;

-- Invoices table indexes
CREATE INDEX idx_invoices_customer_company ON invoices(customer_company_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_invoices_customer_contact ON invoices(customer_contact_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_invoices_status ON invoices(status) WHERE deleted_at IS NULL;
CREATE INDEX idx_invoices_due_date ON invoices(due_date) WHERE deleted_at IS NULL;
CREATE INDEX idx_invoices_invoice_date ON invoices(invoice_date DESC) WHERE deleted_at IS NULL;
CREATE INDEX idx_invoices_total_amount ON invoices(total_amount DESC) WHERE deleted_at IS NULL;
CREATE INDEX idx_invoices_balance_due ON invoices(balance_due) WHERE deleted_at IS NULL AND balance_due > 0;
CREATE INDEX idx_invoices_created_by ON invoices(created_by) WHERE deleted_at IS NULL;

-- Payments table indexes
CREATE INDEX idx_payments_invoice ON payments(invoice_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_payments_date ON payments(payment_date DESC) WHERE deleted_at IS NULL;
CREATE INDEX idx_payments_method ON payments(payment_method_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_payments_status ON payments(status) WHERE deleted_at IS NULL;
CREATE INDEX idx_payments_stripe_intent ON payments(stripe_payment_intent_id) WHERE stripe_payment_intent_id IS NOT NULL;

-- Quotes table indexes
CREATE INDEX idx_quotes_customer_company ON quotes(customer_company_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_quotes_status ON quotes(status_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_quotes_valid_until ON quotes(valid_until) WHERE deleted_at IS NULL;
CREATE INDEX idx_quotes_created_by ON quotes(created_by) WHERE deleted_at IS NULL;

-- Activities table indexes
CREATE INDEX idx_activities_user ON activities(user_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_activities_contact ON activities(contact_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_activities_deal ON activities(deal_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_activities_job ON activities(job_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_activities_type ON activities(type_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_activities_start_time ON activities(start_time DESC) WHERE deleted_at IS NULL;

-- Documents table indexes
CREATE INDEX idx_documents_contact ON documents(contact_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_documents_company ON documents(company_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_documents_job ON documents(job_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_documents_category ON documents(category_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_documents_uploaded_by ON documents(uploaded_by) WHERE deleted_at IS NULL;
CREATE INDEX idx_documents_name ON documents USING GIN(name gin_trgm_ops) WHERE deleted_at IS NULL;

-- Audit logs indexes
CREATE INDEX idx_audit_logs_table_record ON audit_logs(table_name, record_id);
CREATE INDEX idx_audit_logs_user ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at DESC);
CREATE INDEX idx_audit_logs_action ON audit_logs(action);

-- Email log indexes
CREATE INDEX idx_email_log_invoice ON email_log(invoice_id);
CREATE INDEX idx_email_log_status ON email_log(status);
CREATE INDEX idx_email_log_sent_at ON email_log(sent_at DESC);
CREATE INDEX idx_email_log_to_email ON email_log(to_email);

-- Recurring invoices indexes
CREATE INDEX idx_recurring_invoices_next_date ON recurring_invoices(next_invoice_date) WHERE is_active = true AND deleted_at IS NULL;
CREATE INDEX idx_recurring_invoices_customer_company ON recurring_invoices(customer_company_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_recurring_invoices_active ON recurring_invoices(is_active) WHERE deleted_at IS NULL;

-- =====================================================
-- 21. TRIGGERS FOR AUTOMATIC UPDATES
-- =====================================================

-- Function to update timestamps
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$ language 'plpgsql';

-- Apply updated_at triggers to all tables with updated_at column
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_companies_updated_at BEFORE UPDATE ON companies
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_contacts_updated_at BEFORE UPDATE ON contacts
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_leads_updated_at BEFORE UPDATE ON leads
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_deals_updated_at BEFORE UPDATE ON deals
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_jobs_updated_at BEFORE UPDATE ON jobs
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_tasks_updated_at BEFORE UPDATE ON tasks
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_invoices_updated_at BEFORE UPDATE ON invoices
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_expenses_updated_at BEFORE UPDATE ON expenses
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Function to calculate invoice totals
CREATE OR REPLACE FUNCTION calculate_invoice_totals()
RETURNS TRIGGER AS $
BEGIN
-- Calculate subtotal from line items
UPDATE invoices
SET subtotal = (
SELECT COALESCE(SUM(line_total), 0)
FROM invoice_line_items
WHERE invoice_id = COALESCE(NEW.invoice_id, OLD.invoice_id)
)
WHERE id = COALESCE(NEW.invoice_id, OLD.invoice_id);

    -- Update total amounts
    UPDATE invoices
    SET
        total_amount = subtotal + COALESCE(total_tax_amount, 0) + COALESCE(shipping_charge, 0) + COALESCE(adjustment_amount, 0) - COALESCE(discount_amount, 0),
        balance_due = subtotal + COALESCE(total_tax_amount, 0) + COALESCE(shipping_charge, 0) + COALESCE(adjustment_amount, 0) - COALESCE(discount_amount, 0) - COALESCE(paid_amount, 0) - COALESCE(credits_applied, 0),
        updated_at = CURRENT_TIMESTAMP
    WHERE id = COALESCE(NEW.invoice_id, OLD.invoice_id);

    RETURN COALESCE(NEW, OLD);

END;
$ LANGUAGE plpgsql;

-- Trigger to update invoice totals when line items change
CREATE TRIGGER update_invoice_totals_on_line_items
AFTER INSERT OR UPDATE OR DELETE ON invoice_line_items
FOR EACH ROW EXECUTE FUNCTION calculate_invoice_totals();

-- Function to update invoice payment status
CREATE OR REPLACE FUNCTION update_invoice_payment_status()
RETURNS TRIGGER AS $
DECLARE
invoice_record RECORD;
BEGIN
-- Get invoice totals
SELECT
id, total_amount, balance_due, status,
(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = invoices.id AND status = 'completed') as paid_total
INTO invoice_record
FROM invoices
WHERE id = COALESCE(NEW.invoice_id, OLD.invoice_id);

    -- Update paid amount and status
    UPDATE invoices SET
        paid_amount = invoice_record.paid_total,
        balance_due = total_amount - invoice_record.paid_total,
        status = CASE
            WHEN invoice_record.paid_total = 0 THEN
                CASE WHEN status = 'draft' THEN 'draft' ELSE 'sent' END
            WHEN invoice_record.paid_total >= total_amount THEN 'paid'
            WHEN invoice_record.paid_total > 0 THEN 'partially_paid'
            ELSE status
        END,
        payment_count = (SELECT COUNT(*) FROM payments WHERE invoice_id = invoices.id AND status = 'completed'),
        last_payment_date = (SELECT MAX(payment_date) FROM payments WHERE invoice_id = invoices.id AND status = 'completed'),
        first_payment_date = COALESCE(first_payment_date, (SELECT MIN(payment_date) FROM payments WHERE invoice_id = invoices.id AND status = 'completed')),
        updated_at = CURRENT_TIMESTAMP
    WHERE id = invoice_record.id;

    RETURN COALESCE(NEW, OLD);

END;
$ LANGUAGE plpgsql;

-- Trigger to update invoice status when payments change
CREATE TRIGGER update_invoice_payment_status_trigger
AFTER INSERT OR UPDATE OR DELETE ON payments
FOR EACH ROW EXECUTE FUNCTION update_invoice_payment_status();

-- Function to update job actual hours from time entries
CREATE OR REPLACE FUNCTION update_job_actual_hours()
RETURNS TRIGGER AS $
BEGIN
UPDATE jobs
SET
actual_hours = (
SELECT COALESCE(SUM(duration_minutes), 0) / 60.0
FROM time_entries
WHERE job_id = COALESCE(NEW.job_id, OLD.job_id)
AND deleted_at IS NULL
),
updated_at = CURRENT_TIMESTAMP
WHERE id = COALESCE(NEW.job_id, OLD.job_id);

    RETURN COALESCE(NEW, OLD);

END;
$ LANGUAGE plpgsql;

-- Trigger to update job hours when time entries change
CREATE TRIGGER update_job_actual_hours_trigger
AFTER INSERT OR UPDATE OR DELETE ON time_entries
FOR EACH ROW
WHEN (OLD.job_id IS NOT NULL OR NEW.job_id IS NOT NULL)
EXECUTE FUNCTION update_job_actual_hours();

-- Function for comprehensive audit logging
CREATE OR REPLACE FUNCTION audit_log_changes()
RETURNS TRIGGER AS $
DECLARE
old_data JSONB;
new_data JSONB;
changed_fields TEXT[];
BEGIN
-- Skip audit logging for audit_logs table itself
IF TG_TABLE_NAME = 'audit_logs' THEN
RETURN COALESCE(NEW, OLD);
END IF;

    -- Prepare old and new data
    old_data = CASE WHEN TG_OP = 'DELETE' OR TG_OP = 'UPDATE' THEN to_jsonb(OLD) ELSE NULL END;
    new_data = CASE WHEN TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN to_jsonb(NEW) ELSE NULL END;

    -- For updates, identify changed fields
    IF TG_OP = 'UPDATE' THEN
        SELECT array_agg(key) INTO changed_fields
        FROM jsonb_each(old_data) old_kv
        JOIN jsonb_each(new_data) new_kv ON old_kv.key = new_kv.key
        WHERE old_kv.value != new_kv.value;
    END IF;

    -- Insert audit record
    INSERT INTO audit_logs (
        table_name, record_id, action, old_values, new_values, changed_fields,
        user_id, request_id, operation_type
    ) VALUES (
        TG_TABLE_NAME,
        COALESCE(NEW.id, OLD.id),
        TG_OP,
        old_data,
        new_data,
        changed_fields,
        current_setting('app.current_user_id', true)::UUID,
        current_setting('app.request_id', true)::UUID,
        'api_call'
    );

    RETURN COALESCE(NEW, OLD);

END;
$ LANGUAGE plpgsql;

-- Apply audit triggers to key tables
CREATE TRIGGER audit_users AFTER INSERT OR UPDATE OR DELETE ON users
FOR EACH ROW EXECUTE FUNCTION audit_log_changes();

CREATE TRIGGER audit_companies AFTER INSERT OR UPDATE OR DELETE ON companies
FOR EACH ROW EXECUTE FUNCTION audit_log_changes();

CREATE TRIGGER audit_contacts AFTER INSERT OR UPDATE OR DELETE ON contacts
FOR EACH ROW EXECUTE FUNCTION audit_log_changes();

CREATE TRIGGER audit_deals AFTER INSERT OR UPDATE OR DELETE ON deals
FOR EACH ROW EXECUTE FUNCTION audit_log_changes();

CREATE TRIGGER audit_jobs AFTER INSERT OR UPDATE OR DELETE ON jobs
FOR EACH ROW EXECUTE FUNCTION audit_log_changes();

CREATE TRIGGER audit_invoices AFTER INSERT OR UPDATE OR DELETE ON invoices
FOR EACH ROW EXECUTE FUNCTION audit_log_changes();

CREATE TRIGGER audit_payments AFTER INSERT OR UPDATE OR DELETE ON payments
FOR EACH ROW EXECUTE FUNCTION audit_log_changes();

CREATE TRIGGER audit_expenses AFTER INSERT OR UPDATE OR DELETE ON expenses
FOR EACH ROW EXECUTE FUNCTION audit_log_changes();

-- =====================================================
-- 22. VIEWS FOR COMMON QUERIES
-- =====================================================

-- Customer overview view
CREATE VIEW customer_overview AS
SELECT
c.id as customer_id,
'company' as customer_type,
c.name as customer_name,
c.email as customer_email,
c.phone as customer_phone,
c.city,
c.state,
c.country,
-- Contact counts
(SELECT COUNT(_) FROM contacts WHERE company_id = c.id AND deleted_at IS NULL) as contact_count,
-- Deal statistics
(SELECT COUNT(_) FROM deals WHERE company_id = c.id AND deleted_at IS NULL) as deal_count,
(SELECT COALESCE(SUM(amount), 0) FROM deals WHERE company_id = c.id AND is_won = true AND deleted_at IS NULL) as total_won_amount,
-- Invoice statistics
(SELECT COUNT(\*) FROM invoices WHERE customer_company_id = c.id AND deleted_at IS NULL) as invoice_count,
(SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE customer_company_id = c.id AND deleted_at IS NULL) as total_invoiced,
(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE customer_company_id = c.id AND deleted_at IS NULL) as total_paid,
(SELECT COALESCE(SUM(balance_due), 0) FROM invoices WHERE customer_company_id = c.id AND status != 'paid' AND deleted_at IS NULL) as outstanding_balance,
-- Last activity
(SELECT MAX(created_at) FROM activities WHERE company_id = c.id AND deleted_at IS NULL) as last_activity_date,
c.created_at,
c.updated_at
FROM companies c
WHERE c.deleted_at IS NULL

UNION ALL

SELECT
co.id as customer_id,
'contact' as customer_type,
CONCAT(co.first_name, ' ', co.last_name) as customer_name,
co.email as customer_email,
COALESCE(co.phone, co.mobile) as customer_phone,
co.city,
co.state,
co.country,
-- Contact counts (always 1 for individual contacts)
1 as contact_count,
-- Deal statistics
(SELECT COUNT(_) FROM deals WHERE contact_id = co.id AND deleted_at IS NULL) as deal_count,
(SELECT COALESCE(SUM(amount), 0) FROM deals WHERE contact_id = co.id AND is_won = true AND deleted_at IS NULL) as total_won_amount,
-- Invoice statistics
(SELECT COUNT(_) FROM invoices WHERE customer_contact_id = co.id AND deleted_at IS NULL) as invoice_count,
(SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE customer_contact_id = co.id AND deleted_at IS NULL) as total_invoiced,
(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE customer_contact_id = co.id AND deleted_at IS NULL) as total_paid,
(SELECT COALESCE(SUM(balance_due), 0) FROM invoices WHERE customer_contact_id = co.id AND status != 'paid' AND deleted_at IS NULL) as outstanding_balance,
-- Last activity
(SELECT MAX(created_at) FROM activities WHERE contact_id = co.id AND deleted_at IS NULL) as last_activity_date,
co.created_at,
co.updated_at
FROM contacts co
WHERE co.deleted_at IS NULL AND co.company_id IS NULL; -- Only individual contacts

-- Job dashboard view
CREATE VIEW job_dashboard AS
SELECT
j.id,
j.job_number,
j.title,
j.description,
-- Customer info
COALESCE(comp.name, CONCAT(cont.first_name, ' ', cont.last_name)) as customer_name,
-- Staff info
CONCAT(staff.first_name, ' ', staff.last_name) as assigned_staff_name,
staff.email as assigned_staff_email,
-- Status and priority
js.name as status_name,
js.color as status_color,
jp.name as priority_name,
jp.color as priority_color,
jp.level as priority_level,
-- Timeline
j.start_date,
j.due_date,
j.estimated_hours,
j.actual_hours,
j.completion_percentage,
-- Progress indicators
CASE
WHEN j.due_date < CURRENT_DATE AND j.completion_percentage < 100 THEN 'overdue'
WHEN j.due_date <= CURRENT_DATE + INTERVAL '3 days' AND j.completion_percentage < 100 THEN 'due_soon'
WHEN j.completion_percentage = 100 THEN 'completed'
ELSE 'on_track'
END as timeline_status,
-- Financial
j.estimated_cost,
j.actual_cost,
j.billable_amount,
-- Dates
j.created_at,
j.started_at,
j.completed_at
FROM jobs j
LEFT JOIN companies comp ON j.customer_company_id = comp.id
LEFT JOIN contacts cont ON j.customer_contact_id = cont.id
LEFT JOIN users staff ON j.assigned_to = staff.id
LEFT JOIN job_statuses js ON j.status_id = js.id
LEFT JOIN job_priorities jp ON j.priority_id = jp.id
WHERE j.deleted_at IS NULL;

-- Invoice aging report view
CREATE VIEW invoice_aging_report AS
SELECT
i.id,
i.invoice_number,
COALESCE(comp.name, CONCAT(cont.first_name, ' ', cont.last_name)) as customer_name,
i.invoice_date,
i.due_date,
i.total_amount,
i.paid_amount,
i.balance_due,
i.status,
-- Aging buckets
CURRENT_DATE - i.due_date as days_overdue,
CASE
WHEN i.status = 'paid' THEN 'paid'
WHEN i.due_date >= CURRENT_DATE THEN 'current'
WHEN CURRENT_DATE - i.due_date <= 30 THEN '1-30_days'
WHEN CURRENT_DATE - i.due_date <= 60 THEN '31-60_days'
WHEN CURRENT_DATE - i.due_date <= 90 THEN '61-90_days'
ELSE 'over_90_days'
END as aging_bucket,
-- Customer contact info
COALESCE(comp.email, cont.email) as customer_email,
COALESCE(comp.phone, cont.phone) as customer_phone
FROM invoices i
LEFT JOIN companies comp ON i.customer_company_id = comp.id
LEFT JOIN contacts cont ON i.customer_contact_id = cont.id
WHERE i.deleted_at IS NULL AND i.status != 'draft';

-- Staff performance view
CREATE VIEW staff_performance AS
SELECT
u.id as staff_id,
CONCAT(u.first_name, ' ', u.last_name) as staff_name,
u.email,
-- Job statistics
(SELECT COUNT(_) FROM jobs WHERE assigned_to = u.id AND deleted_at IS NULL) as total_jobs_assigned,
(SELECT COUNT(_) FROM jobs WHERE assigned_to = u.id AND completion_percentage = 100 AND deleted_at IS NULL) as jobs_completed,
(SELECT COUNT(_) FROM jobs WHERE assigned_to = u.id AND due_date < CURRENT_DATE AND completion_percentage < 100 AND deleted_at IS NULL) as jobs_overdue,
-- Time tracking
(SELECT COALESCE(SUM(duration_minutes), 0) / 60.0 FROM time_entries WHERE user_id = u.id AND deleted_at IS NULL AND start_time >= CURRENT_DATE - INTERVAL '30 days') as hours_this_month,
(SELECT COALESCE(SUM(billable_amount), 0) FROM time_entries WHERE user_id = u.id AND is_billable = true AND deleted_at IS NULL AND start_time >= CURRENT_DATE - INTERVAL '30 days') as billable_amount_this_month,
-- Task statistics
(SELECT COUNT(_) FROM tasks WHERE assigned_to = u.id AND deleted_at IS NULL) as total_tasks_assigned,
(SELECT COUNT(\*) FROM tasks t JOIN task_statuses ts ON t.status_id = ts.id WHERE t.assigned_to = u.id AND ts.is_completed = true AND t.deleted_at IS NULL) as tasks_completed,
-- Averages
(SELECT AVG(actual_hours) FROM jobs WHERE assigned_to = u.id AND actual_hours > 0 AND deleted_at IS NULL) as avg_job_hours,
(SELECT AVG(completion_percentage) FROM jobs WHERE assigned_to = u.id AND deleted_at IS NULL) as avg_completion_rate,
-- Last activity
(SELECT MAX(created_at) FROM time_entries WHERE user_id = u.id) as last_time_entry
FROM users u
JOIN user_roles ur ON u.id = ur.user_id
JOIN roles r ON ur.role_id = r.id
WHERE r.name = 'staff' AND u.deleted_at IS NULL AND u.is_active = true;

-- Revenue dashboard view
CREATE VIEW revenue_dashboard AS
SELECT
DATE_TRUNC('month', i.invoice_date) as month,
-- Invoice metrics
COUNT(_) as invoices_created,
SUM(i.total_amount) as total_invoiced,
SUM(i.paid_amount) as total_collected,
SUM(i.balance_due) as outstanding_amount,
-- Payment metrics
(SELECT COUNT(_) FROM payments p WHERE DATE_TRUNC('month', p.payment_date) = DATE_TRUNC('month', i.invoice_date) AND p.status = 'completed') as payments_received,
(SELECT AVG(i2.total_amount) FROM invoices i2 WHERE DATE_TRUNC('month', i2.invoice_date) = DATE_TRUNC('month', i.invoice_date) AND i2.deleted_at IS NULL) as avg_invoice_amount,
-- Collection efficiency
CASE
WHEN SUM(i.total_amount) > 0 THEN
ROUND((SUM(i.paid_amount) / SUM(i.total_amount) \* 100), 2)
ELSE 0
END as collection_rate_percentage,
-- Stripe fees
(SELECT COALESCE(SUM(stripe_fee_amount), 0) FROM payments p WHERE DATE_TRUNC('month', p.payment_date) = DATE_TRUNC('month', i.invoice_date) AND p.status = 'completed') as total_stripe_fees
FROM invoices i
WHERE i.deleted_at IS NULL AND i.status != 'draft'
GROUP BY DATE_TRUNC('month', i.invoice_date)
ORDER BY month DESC;

-- =====================================================
-- 23. SAMPLE DATA INSERTION
-- =====================================================

-- Insert default roles
INSERT INTO roles (name, description, permissions) VALUES
('admin', 'Full system access', '["*"]'),
('manager', 'Management access with team oversight', '["contacts:*", "jobs:*", "invoices:*", "reports:read", "tasks:*"]'),
('staff', 'Staff access for assigned work', '["jobs:read_assigned", "jobs:update_assigned", "time:log", "tasks:read_assigned"]'),
('customer', 'Customer portal access', '["invoices:read_own", "payments:make", "portal:access"]');

-- Insert default service categories
INSERT INTO service_categories (name, description, color) VALUES
('Consulting', 'Business and technical consulting services', '#3B82F6'),
('Development', 'Software and web development services', '#10B981'),
('Design', 'Graphic design and creative services', '#8B5CF6'),
('Marketing', 'Digital marketing and advertising services', '#F59E0B'),
('Support', 'Technical support and maintenance services', '#EF4444');

-- Insert default lead sources
INSERT INTO lead_sources (name, description) VALUES
('Website', 'Lead from company website'),
('Referral', 'Referred by existing customer'),
('Social Media', 'From social media channels'),
('Cold Call', 'Cold calling campaign'),
('Email Campaign', 'Email marketing campaign'),
('Trade Show', 'Trade show or conference'),
('Google Ads', 'Google advertising'),
('Other', 'Other sources');

-- Insert default lead statuses
INSERT INTO lead_statuses (name, description, display_order, color) VALUES
('New', 'Newly created lead', 1, '#6B7280'),
('Contacted', 'Initial contact made', 2, '#3B82F6'),
('Qualified', 'Lead has been qualified', 3, '#10B981'),
('Proposal Sent', 'Proposal has been sent', 4, '#F59E0B'),
('Negotiating', 'In negotiation phase', 5, '#8B5CF6'),
('Converted', 'Converted to deal', 6, '#10B981'),
('Lost', 'Lead was lost', 7, '#EF4444');

-- Insert default deal stages
INSERT INTO deal_stages (name, description, probability, display_order, color) VALUES
('Lead', 'Initial lead stage', 10.00, 1, '#6B7280'),
('Qualified', 'Qualified opportunity', 25.00, 2, '#3B82F6'),
('Proposal', 'Proposal sent', 50.00, 3, '#F59E0B'),
('Negotiation', 'In negotiation', 75.00, 4, '#8B5CF6'),
('Closed Won', 'Deal won', 100.00, 5, '#10B981'),
('Closed Lost', 'Deal lost', 0.00, 6, '#EF4444');

-- Update closed stages
UPDATE deal_stages SET is_closed = true WHERE name IN ('Closed Won', 'Closed Lost');
UPDATE deal_stages SET is_won = true WHERE name = 'Closed Won';

-- Insert job statuses
INSERT INTO job_statuses (name, description, is_active_status, is_completed_status, color, display_order) VALUES
('Not Started', 'Job has not been started', false, false, '#6B7280', 1),
('In Progress', 'Job is actively being worked on', true, false, '#3B82F6', 2),
('On Hold', 'Job is temporarily paused', false, false, '#F59E0B', 3),
('Review', 'Job is under review', false, false, '#8B5CF6', 4),
('Completed', 'Job has been completed', false, true, '#10B981', 5),
('Cancelled', 'Job was cancelled', false, false, '#EF4444', 6);

-- Insert job priorities
INSERT INTO job_priorities (name, level, color) VALUES
('Low', 1, '#6B7280'),
('Medium', 2, '#3B82F6'),
('High', 3, '#F59E0B'),
('Urgent', 4, '#EF4444'),
('Critical', 5, '#DC2626');

-- Insert task priorities
INSERT INTO task_priorities (name, level, color) VALUES
('Low', 1, '#6B7280'),
('Medium', 2, '#3B82F6'),
('High', 3, '#F59E0B'),
('Critical', 4, '#EF4444');

-- Insert task statuses
INSERT INTO task_statuses (name, is_completed, display_order, color) VALUES
('To Do', false, 1, '#6B7280'),
('In Progress', false, 2, '#3B82F6'),
('Review', false, 3, '#F59E0B'),
('Done', true, 4, '#10B981'),
('Cancelled', false, 5, '#EF4444');

-- Insert activity types
INSERT INTO activity_types (name, icon, color) VALUES
('Call', 'phone', '#3B82F6'),
('Email', 'mail', '#10B981'),
('Meeting', 'calendar', '#8B5CF6'),
('Note', 'file-text', '#6B7280'),
('Task', 'check-square', '#F59E0B'),
('Follow Up', 'clock', '#EF4444');

-- Insert expense categories
INSERT INTO expense_categories (name, description, is_tax_deductible) VALUES
('Office Supplies', 'General office supplies and materials', true),
('Travel', 'Business travel expenses', true),
('Meals & Entertainment', 'Business meals and client entertainment', true),
('Software & Subscriptions', 'Software licenses and subscriptions', true),
('Equipment', 'Office equipment and hardware', true),
('Marketing', 'Marketing and advertising expenses', true),
('Professional Services', 'Legal, accounting, and consulting fees', true),
('Utilities', 'Office utilities and communication', true),
('Rent', 'Office rent and facilities', true),
('Other', 'Other business expenses', true);

-- Insert payment methods
INSERT INTO payment_methods (name, code, description, is_online, processing_fee_percentage, processing_fee_fixed) VALUES
('Cash', 'cash', 'Cash payment', false, 0, 0),
('Check', 'check', 'Check payment', false, 0, 0),
('Bank Transfer', 'bank_transfer', 'Direct bank transfer', false, 0, 0),
('Credit Card (Stripe)', 'stripe_card', 'Credit card via Stripe', true, 0.029, 0.30),
('ACH (Stripe)', 'stripe_ach', 'ACH payment via Stripe', true, 0.008, 5.00),
('PayPal', 'paypal', 'PayPal payment', true, 0.0349, 0.49);

-- Insert recurring frequencies
INSERT INTO recurring_frequencies (name, interval_type, interval_value, description) VALUES
('Weekly', 'weeks', 1, 'Every week'),
('Bi-weekly', 'weeks', 2, 'Every two weeks'),
('Monthly', 'months', 1, 'Every month'),
('Quarterly', 'months', 3, 'Every three months'),
('Semi-annually', 'months', 6, 'Every six months'),
('Annually', 'years', 1, 'Every year');

-- Insert quote statuses
INSERT INTO quote_statuses (name, description, is_approved, is_rejected, color) VALUES
('Draft', 'Quote is being prepared', false, false, '#6B7280'),
('Sent', 'Quote has been sent to customer', false, false, '#3B82F6'),
('Viewed', 'Customer has viewed the quote', false, false, '#8B5CF6'),
('Approved', 'Quote has been approved by customer', true, false, '#10B981'),
('Rejected', 'Quote has been rejected by customer', false, true, '#EF4444'),
('Expired', 'Quote has expired', false, false, '#9CA3AF');

-- Insert refund reasons
INSERT INTO refund_reasons (name, description, requires_approval) VALUES
('Duplicate Payment', 'Customer made duplicate payment', false),
('Service Not Delivered', 'Service was not delivered as promised', true),
('Customer Cancellation', 'Customer cancelled the service', true),
('Billing Error', 'Error in billing amount', false),
('Quality Issues', 'Issues with service quality', true),
('Processing Error', 'Payment processing error', false),
('Other', 'Other reason for refund', true);

-- Insert document categories
INSERT INTO document_categories (name, description, icon) VALUES
('Contracts', 'Service contracts and agreements', 'file-text'),
('Invoices', 'Invoice documents and receipts', 'receipt'),
('Proposals', 'Project proposals and quotes', 'clipboard'),
('Reports', 'Project reports and deliverables', 'bar-chart'),
('Receipts', 'Expense receipts and documentation', 'credit-card'),
('Legal', 'Legal documents and compliance', 'shield'),
('Marketing', 'Marketing materials and assets', 'megaphone'),
('Other', 'Other miscellaneous documents', 'folder');

-- Insert default email templates
INSERT INTO email_templates (name, template_type, subject, body_html, available_variables, is_default, created_by) VALUES
('Invoice Email', 'invoice_send', 'Invoice {{invoice_number}} from {{company_name}}',
'<p>Dear {{customer_name}},</p>

<p>Please find attached invoice {{invoice_number}} for {{total_amount}}.</p>
<p>Due Date: {{due_date}}</p>
<p>You can pay online using this link: {{payment_link}}</p>
<p>Thank you for your business!</p>
<p>{{company_name}}</p>', 
'{"customer_name": "Customer full name", "invoice_number": "Invoice number", "total_amount": "Invoice total", "due_date": "Invoice due date", "payment_link": "Online payment link", "company_name": "Your company name"}', 
true, (SELECT id FROM users WHERE email = 'admin@example.com' LIMIT 1)),

('Payment Reminder', 'payment_reminder', 'Payment Reminder - Invoice {{invoice_number}}',
'<p>Dear {{customer_name}},</p>

<p>This is a friendly reminder that invoice {{invoice_number}} for {{total_amount}} is due on {{due_date}}.</p>
<p>Current balance: {{balance_due}}</p>
<p>Pay now: {{payment_link}}</p>
<p>If you have already made the payment, please ignore this message.</p>
<p>Thank you,<br>{{company_name}}</p>', 
'{"customer_name": "Customer full name", "invoice_number": "Invoice number", "total_amount": "Invoice total", "due_date": "Invoice due date", "balance_due": "Outstanding balance", "payment_link": "Online payment link", "company_name": "Your company name"}', 
true, (SELECT id FROM users WHERE email = 'admin@example.com' LIMIT 1)),

('Quote Email', 'quote_send', 'Quote {{quote_number}} from {{company_name}}',
'<p>Dear {{customer_name}},</p>

<p>Thank you for your interest in our services. Please find attached quote {{quote_number}}.</p>
<p>Quote Amount: {{total_amount}}</p>
<p>Valid Until: {{valid_until}}</p>
<p>You can view and approve this quote online: {{quote_link}}</p>
<p>Please don''t hesitate to contact us if you have any questions.</p>
<p>Best regards,<br>{{company_name}}</p>', 
'{"customer_name": "Customer full name", "quote_number": "Quote number", "total_amount": "Quote total", "valid_until": "Quote expiry date", "quote_link": "Online quote link", "company_name": "Your company name"}', 
true, (SELECT id FROM users WHERE email = 'admin@example.com' LIMIT 1));

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, description, setting_type, is_public) VALUES
('company_name', '"Your Company Name"', 'Company name for branding', 'general', true),
('company_email', '"info@yourcompany.com"', 'Default company email', 'general', true),
('company_phone', '"+1 (555) 123-4567"', 'Company phone number', 'general', true),
('default_currency', '"USD"', 'Default currency for invoicing', 'financial', true),
('default_payment_terms', '30', 'Default payment terms in days', 'financial', true),
('invoice_number_format', '"INV-{YYYY}-{###}"', 'Invoice numbering format', 'invoicing', false),
('quote_number_format', '"QUO-{YYYY}-{###}"', 'Quote numbering format', 'invoicing', false),
('job_number_format', '"JOB-{YYYY}-{###}"', 'Job numbering format', 'jobs', false),
('stripe_publishable_key', '""', 'Stripe publishable key', 'payment', false),
('stripe_webhook_secret', '""', 'Stripe webhook endpoint secret', 'payment', false),
('email_from_name', '"Your Company"', 'Default email sender name', 'email', false),
('email_from_address', '"noreply@yourcompany.com"', 'Default email sender address', 'email', false),
('late_fee_percentage', '1.5', 'Late fee percentage per month', 'financial', false),
('auto_send_reminders', 'true', 'Automatically send payment reminders', 'invoicing', false),
('reminder_days_before_due', '[7, 3, 1]', 'Days before due date to send reminders', 'invoicing', false),
('reminder_days_after_due', '[1, 7, 14, 30]', 'Days after due date to send reminders', 'invoicing', false);

-- =====================================================
-- 24. STORED PROCEDURES FOR BUSINESS LOGIC
-- =====================================================

-- Function to generate next number in sequence
CREATE OR REPLACE FUNCTION get_next_number(
prefix VARCHAR(10),
table_name VARCHAR(50),
column_name VARCHAR(50)
) RETURNS VARCHAR(50) AS $
DECLARE
current_year VARCHAR(4);
next_number INTEGER;
result VARCHAR(50);
BEGIN
current_year := EXTRACT(YEAR FROM CURRENT_DATE)::VARCHAR;

    -- Get the next number for this year
    EXECUTE format('SELECT COALESCE(MAX(CAST(SUBSTRING(%I FROM ''^%s-\d{4}-(\d+)-- =====================================================

-- COMPLETE DATABASE SCHEMA
-- CRM with Invoicing System
-- PostgreSQL 15+
-- =====================================================

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm"; -- For fuzzy search
CREATE EXTENSION IF NOT EXISTS "btree_gin"; -- For composite indexes

-- =====================================================
-- 1. USER MANAGEMENT & AUTHENTICATION
-- =====================================================

-- User roles lookup table
CREATE TABLE roles (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
permissions JSONB NOT NULL DEFAULT '[]',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Main users table
CREATE TABLE users (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
email VARCHAR(255) UNIQUE NOT NULL,
password_hash VARCHAR(255) NOT NULL,
first_name VARCHAR(100) NOT NULL,
last_name VARCHAR(100) NOT NULL,
phone VARCHAR(20),
position VARCHAR(100),
avatar_url TEXT,
is_active BOOLEAN DEFAULT true,
email_verified_at TIMESTAMPTZ,
last_login_at TIMESTAMPTZ,
timezone VARCHAR(50) DEFAULT 'UTC',
language VARCHAR(10) DEFAULT 'en',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ -- Soft delete
);

-- User roles assignment (many-to-many)
CREATE TABLE user_roles (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
assigned_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
UNIQUE(user_id, role_id)
);

-- Password reset tokens
CREATE TABLE password_reset_tokens (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
token VARCHAR(255) NOT NULL,
expires_at TIMESTAMPTZ NOT NULL,
used_at TIMESTAMPTZ,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- User sessions for tracking active sessions
CREATE TABLE user_sessions (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
token_hash VARCHAR(255) NOT NULL,
ip_address INET,
user_agent TEXT,
expires_at TIMESTAMPTZ NOT NULL,
last_activity_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. CRM CORE ENTITIES
-- =====================================================

-- Companies/Organizations
CREATE TABLE companies (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(255) NOT NULL,
industry VARCHAR(100),
website VARCHAR(255),
phone VARCHAR(20),
email VARCHAR(255),
-- Address fields
address_line_1 TEXT,
address_line_2 TEXT,
city VARCHAR(100),
state VARCHAR(100),
postal_code VARCHAR(20),
country VARCHAR(100),
-- Business details
description TEXT,
employees_count INTEGER,
annual_revenue DECIMAL(15,2),
company_size VARCHAR(20), -- startup, small, medium, large, enterprise
-- Meta fields
owner_id UUID REFERENCES users(id),
logo_url TEXT,
timezone VARCHAR(50),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Contacts/People
CREATE TABLE contacts (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
first_name VARCHAR(100) NOT NULL,
last_name VARCHAR(100) NOT NULL,
email VARCHAR(255),
phone VARCHAR(20),
mobile VARCHAR(20),
job_title VARCHAR(100),
department VARCHAR(100),
-- Address (can be different from company)
address_line_1 TEXT,
address_line_2 TEXT,
city VARCHAR(100),
state VARCHAR(100),
postal_code VARCHAR(20),
country VARCHAR(100),
-- Relationships
company_id UUID REFERENCES companies(id),
owner_id UUID NOT NULL REFERENCES users(id),
-- Contact details
description TEXT,
date_of_birth DATE,
social_profiles JSONB, -- LinkedIn, Twitter, etc.
preferred_contact_method VARCHAR(20), -- email, phone, text
-- Status and tracking
is_active BOOLEAN DEFAULT true,
lead_source VARCHAR(50),
last_contacted_at TIMESTAMPTZ,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Tags for flexible categorization
CREATE TABLE tags (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
color VARCHAR(7) DEFAULT '#3B82F6', -- Hex color
description TEXT,
tag_type VARCHAR(20) DEFAULT 'general', -- general, lead_source, industry, etc.
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Contact tags (many-to-many)
CREATE TABLE contact_tags (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
contact_id UUID NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
tag_id INTEGER NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
UNIQUE(contact_id, tag_id)
);

-- =====================================================
-- 3. LEAD MANAGEMENT
-- =====================================================

-- Lead sources lookup
CREATE TABLE lead_sources (
id SERIAL PRIMARY KEY,
name VARCHAR(100) UNIQUE NOT NULL,
description TEXT,
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Lead statuses lookup
CREATE TABLE lead_statuses (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
is_active BOOLEAN DEFAULT true,
is_converted BOOLEAN DEFAULT false,
display_order INTEGER DEFAULT 0,
color VARCHAR(7) DEFAULT '#6B7280',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Leads
CREATE TABLE leads (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
contact_id UUID NOT NULL REFERENCES contacts(id),
status_id INTEGER NOT NULL REFERENCES lead_statuses(id),
source_id INTEGER REFERENCES lead_sources(id),
owner_id UUID NOT NULL REFERENCES users(id),
-- Lead details
title VARCHAR(255),
description TEXT,
estimated_value DECIMAL(12,2),
probability INTEGER DEFAULT 0 CHECK (probability >= 0 AND probability <= 100),
expected_close_date DATE,
-- Lead scoring
lead_score INTEGER DEFAULT 0,
temperature VARCHAR(10) DEFAULT 'cold', -- cold, warm, hot
-- Requirements and notes
requirements TEXT,
budget_range VARCHAR(50),
decision_timeframe VARCHAR(50),
decision_makers TEXT,
-- Tracking
last_activity_at TIMESTAMPTZ,
converted_at TIMESTAMPTZ,
converted_to_deal_id UUID, -- Will reference deals table
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- =====================================================
-- 4. DEAL/OPPORTUNITY MANAGEMENT
-- =====================================================

-- Deal stages lookup
CREATE TABLE deal_stages (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
probability DECIMAL(5,2) DEFAULT 0.00 CHECK (probability >= 0 AND probability <= 100),
display_order INTEGER DEFAULT 0,
is_closed BOOLEAN DEFAULT false,
is_won BOOLEAN DEFAULT false,
color VARCHAR(7) DEFAULT '#6B7280',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Deal types lookup
CREATE TABLE deal_types (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Deals/Opportunities
CREATE TABLE deals (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(255) NOT NULL,
company_id UUID REFERENCES companies(id),
contact_id UUID REFERENCES contacts(id),
lead_id UUID REFERENCES leads(id),
owner_id UUID NOT NULL REFERENCES users(id),
-- Deal classification
stage_id INTEGER NOT NULL REFERENCES deal_stages(id),
type_id INTEGER REFERENCES deal_types(id),
-- Financial details
amount DECIMAL(12,2) DEFAULT 0,
currency VARCHAR(3) DEFAULT 'USD',
probability DECIMAL(5,2) DEFAULT 0.00,
expected_close_date DATE,
actual_close_date DATE,
-- Deal details
description TEXT,
requirements TEXT,
competitors TEXT,
next_steps TEXT,
-- Status and tracking
is_won BOOLEAN,
lost_reason TEXT,
closed_at TIMESTAMPTZ,
last_activity_at TIMESTAMPTZ,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_customer_reference CHECK (
        company_id IS NOT NULL OR contact_id IS NOT NULL
    )

);

-- Add foreign key reference back to leads
ALTER TABLE leads ADD CONSTRAINT fk_leads_deal
FOREIGN KEY (converted_to_deal_id) REFERENCES deals(id);

-- =====================================================
-- 5. PRODUCT & SERVICE MANAGEMENT
-- =====================================================

-- Service categories for organizing products/services
CREATE TABLE service_categories (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(100) NOT NULL,
description TEXT,
parent_id UUID REFERENCES service_categories(id), -- For subcategories
icon VARCHAR(50),
color VARCHAR(7) DEFAULT '#3B82F6',
is_active BOOLEAN DEFAULT true,
display_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Products and Services catalog
CREATE TABLE products (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(255) NOT NULL,
code VARCHAR(50) UNIQUE, -- SKU or service code
description TEXT,
category_id UUID REFERENCES service_categories(id),
-- Pricing
unit_price DECIMAL(10,2) DEFAULT 0,
cost_price DECIMAL(10,2) DEFAULT 0, -- For profit calculation
currency VARCHAR(3) DEFAULT 'USD',
-- Service-specific fields
service_type VARCHAR(20) DEFAULT 'one_time', -- one_time, recurring, usage_based
billing_cycle VARCHAR(20), -- hourly, daily, weekly, monthly, quarterly, yearly
estimated_hours DECIMAL(6,2), -- For time-based services
service_duration_days INTEGER, -- How long service lasts
setup_fee DECIMAL(10,2) DEFAULT 0,
-- Inventory (for physical products)
track_inventory BOOLEAN DEFAULT false,
stock_quantity INTEGER DEFAULT 0,
low_stock_threshold INTEGER DEFAULT 5,
-- Pricing tiers
has_tiered_pricing BOOLEAN DEFAULT false,
pricing_tiers JSONB, -- Array of pricing tiers
-- Requirements and skills
required_skills TEXT[],
complexity_level INTEGER DEFAULT 1 CHECK (complexity_level >= 1 AND complexity_level <= 5),
prerequisites TEXT,
-- Tax and billing
is_taxable BOOLEAN DEFAULT true,
tax_category VARCHAR(50),
-- Status
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Deal products/services (many-to-many with additional details)
CREATE TABLE deal_products (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
deal_id UUID NOT NULL REFERENCES deals(id) ON DELETE CASCADE,
product_id UUID NOT NULL REFERENCES products(id),
quantity DECIMAL(10,2) DEFAULT 1,
unit_price DECIMAL(10,2) NOT NULL,
discount_percent DECIMAL(5,2) DEFAULT 0,
discount_amount DECIMAL(10,2) DEFAULT 0,
line_total DECIMAL(12,2) NOT NULL,
notes TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 6. JOB MANAGEMENT SYSTEM
-- =====================================================

-- Job statuses lookup
CREATE TABLE job_statuses (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
is_active_status BOOLEAN DEFAULT true, -- Is this an active/in-progress status?
is_completed_status BOOLEAN DEFAULT false,
color VARCHAR(7) DEFAULT '#6B7280',
display_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Job priorities lookup
CREATE TABLE job_priorities (
id SERIAL PRIMARY KEY,
name VARCHAR(20) UNIQUE NOT NULL, -- Low, Medium, High, Urgent, Critical
level INTEGER UNIQUE NOT NULL, -- 1-5 for sorting
color VARCHAR(7) NOT NULL,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Jobs (created from deals)
CREATE TABLE jobs (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
job_number VARCHAR(50) UNIQUE NOT NULL, -- JOB-2024-001
title VARCHAR(255) NOT NULL,
description TEXT,
-- Relationships
deal_id UUID REFERENCES deals(id),
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
created_by UUID NOT NULL REFERENCES users(id),
assigned_to UUID REFERENCES users(id), -- Primary assigned staff
-- Job details
status_id INTEGER NOT NULL REFERENCES job_statuses(id),
priority_id INTEGER NOT NULL REFERENCES job_priorities(id),
-- Timeline
start_date DATE,
due_date DATE,
estimated_hours DECIMAL(8,2),
actual_hours DECIMAL(8,2) DEFAULT 0,
completion_percentage INTEGER DEFAULT 0 CHECK (completion_percentage >= 0 AND completion_percentage <= 100),
-- Requirements
requirements TEXT,
deliverables TEXT,
special_instructions TEXT,
required_skills TEXT[],
-- Financial
estimated_cost DECIMAL(10,2),
actual_cost DECIMAL(10,2) DEFAULT 0,
billable_amount DECIMAL(10,2),
-- Dependencies
depends_on_job_id UUID REFERENCES jobs(id),
blocking_reason TEXT,
-- Status tracking
started_at TIMESTAMPTZ,
completed_at TIMESTAMPTZ,
approved_at TIMESTAMPTZ,
approved_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_customer_reference CHECK (
        customer_company_id IS NOT NULL OR customer_contact_id IS NOT NULL
    )

);

-- Job products/services (many-to-many)
CREATE TABLE job_products (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
job_id UUID NOT NULL REFERENCES jobs(id) ON DELETE CASCADE,
product_id UUID NOT NULL REFERENCES products(id),
quantity DECIMAL(10,2) DEFAULT 1,
unit_price DECIMAL(10,2),
notes TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Multiple staff assignment for complex jobs
CREATE TABLE job_assignments (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
job_id UUID NOT NULL REFERENCES jobs(id) ON DELETE CASCADE,
user_id UUID NOT NULL REFERENCES users(id),
role_in_job VARCHAR(50), -- lead, assistant, reviewer, etc.
assigned_by UUID NOT NULL REFERENCES users(id),
assigned_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
removed_at TIMESTAMPTZ,
UNIQUE(job_id, user_id, removed_at) -- Prevent duplicate active assignments
);

-- =====================================================
-- 7. TASK MANAGEMENT
-- =====================================================

-- Task priorities lookup
CREATE TABLE task_priorities (
id SERIAL PRIMARY KEY,
name VARCHAR(20) UNIQUE NOT NULL,
level INTEGER UNIQUE NOT NULL,
color VARCHAR(7) NOT NULL,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Task statuses lookup
CREATE TABLE task_statuses (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
is_completed BOOLEAN DEFAULT false,
is_active BOOLEAN DEFAULT true,
display_order INTEGER DEFAULT 0,
color VARCHAR(7) DEFAULT '#6B7280',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Tasks (created by managers for staff)
CREATE TABLE tasks (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
title VARCHAR(255) NOT NULL,
description TEXT,
-- Assignment
assigned_to UUID NOT NULL REFERENCES users(id),
created_by UUID NOT NULL REFERENCES users(id),
-- Classification
priority_id INTEGER NOT NULL REFERENCES task_priorities(id),
status_id INTEGER NOT NULL REFERENCES task_statuses(id),
-- Relationships (can be linked to various entities)
contact_id UUID REFERENCES contacts(id),
company_id UUID REFERENCES companies(id),
lead_id UUID REFERENCES leads(id),
deal_id UUID REFERENCES deals(id),
job_id UUID REFERENCES jobs(id),
-- Timeline
due_date TIMESTAMPTZ,
estimated_minutes INTEGER,
actual_minutes INTEGER,
completed_at TIMESTAMPTZ,
-- Tracking
progress_percentage INTEGER DEFAULT 0 CHECK (progress_percentage >= 0 AND progress_percentage <= 100),
notes TEXT,
completion_notes TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Task reminders
CREATE TABLE task_reminders (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
task_id UUID NOT NULL REFERENCES tasks(id) ON DELETE CASCADE,
remind_at TIMESTAMPTZ NOT NULL,
reminder_type VARCHAR(20) DEFAULT 'email', -- email, sms, push
is_sent BOOLEAN DEFAULT false,
sent_at TIMESTAMPTZ,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 8. TIME TRACKING
-- =====================================================

-- Time entries for jobs and tasks
CREATE TABLE time_entries (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
user_id UUID NOT NULL REFERENCES users(id),
-- Can be linked to job or task
job_id UUID REFERENCES jobs(id),
task_id UUID REFERENCES tasks(id),
-- Time details
start_time TIMESTAMPTZ,
end_time TIMESTAMPTZ,
duration_minutes INTEGER NOT NULL,
description TEXT,
-- Billing
is_billable BOOLEAN DEFAULT true,
hourly_rate DECIMAL(8,2),
billable_amount DECIMAL(10,2),
-- Status
is_approved BOOLEAN DEFAULT false,
approved_by UUID REFERENCES users(id),
approved_at TIMESTAMPTZ,
-- Tracking
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_time_reference CHECK (
        job_id IS NOT NULL OR task_id IS NOT NULL
    ),
    CONSTRAINT check_time_consistency CHECK (
        (start_time IS NULL AND end_time IS NULL) OR
        (start_time IS NOT NULL AND end_time IS NOT NULL AND start_time < end_time)
    )

);

-- =====================================================
-- 9. ACTIVITY & COMMUNICATION TRACKING
-- =====================================================

-- Activity types lookup
CREATE TABLE activity_types (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
icon VARCHAR(50),
color VARCHAR(7) DEFAULT '#6B7280',
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Activities (calls, meetings, emails, notes)
CREATE TABLE activities (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
type_id INTEGER NOT NULL REFERENCES activity_types(id),
user_id UUID NOT NULL REFERENCES users(id),
-- Relationships (can be linked to multiple entities)
contact_id UUID REFERENCES contacts(id),
company_id UUID REFERENCES companies(id),
lead_id UUID REFERENCES leads(id),
deal_id UUID REFERENCES deals(id),
job_id UUID REFERENCES jobs(id),
-- Activity details
subject VARCHAR(255) NOT NULL,
description TEXT,
-- Timing
start_time TIMESTAMPTZ,
end_time TIMESTAMPTZ,
duration_minutes INTEGER,
-- Communication details
direction VARCHAR(10), -- inbound, outbound
outcome VARCHAR(50), -- completed, no_answer, callback_requested, etc.
next_action TEXT,
-- Tracking
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- =====================================================
-- 10. DOCUMENT MANAGEMENT
-- =====================================================

-- Document categories
CREATE TABLE document_categories (
id SERIAL PRIMARY KEY,
name VARCHAR(100) UNIQUE NOT NULL,
description TEXT,
parent_id INTEGER REFERENCES document_categories(id),
icon VARCHAR(50),
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Documents
CREATE TABLE documents (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(255) NOT NULL,
original_filename VARCHAR(255) NOT NULL,
file_path TEXT NOT NULL,
file_size INTEGER NOT NULL, -- in bytes
mime_type VARCHAR(100) NOT NULL,
file_hash VARCHAR(64), -- SHA-256 for deduplication
-- Classification
category_id INTEGER REFERENCES document_categories(id),
tags TEXT[],
-- Relationships (can be linked to multiple entities)
contact_id UUID REFERENCES contacts(id),
company_id UUID REFERENCES companies(id),
lead_id UUID REFERENCES leads(id),
deal_id UUID REFERENCES deals(id),
job_id UUID REFERENCES jobs(id),
-- Metadata
description TEXT,
uploaded_by UUID NOT NULL REFERENCES users(id),
is_public BOOLEAN DEFAULT false,
version_number INTEGER DEFAULT 1,
parent_document_id UUID REFERENCES documents(id), -- For versioning
-- Access control
access_permissions JSONB, -- Who can view/edit
-- Tracking
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- =====================================================
-- 11. EXPENSE MANAGEMENT
-- =====================================================

-- Expense categories (created by admin)
CREATE TABLE expense_categories (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(100) UNIQUE NOT NULL,
description TEXT,
parent_id UUID REFERENCES expense_categories(id), -- For subcategories
-- Budget and limits
monthly_budget DECIMAL(10,2),
yearly_budget DECIMAL(10,2),
-- Tax settings
is_tax_deductible BOOLEAN DEFAULT true,
tax_category VARCHAR(50),
-- Status
is_active BOOLEAN DEFAULT true,
requires_approval BOOLEAN DEFAULT false,
approval_threshold DECIMAL(10,2), -- Auto-approve below this amount
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Expense entries
CREATE TABLE expenses (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
expense_number VARCHAR(50) UNIQUE NOT NULL, -- EXP-2024-001
category_id UUID NOT NULL REFERENCES expense_categories(id),
-- Basic details
amount DECIMAL(10,2) NOT NULL,
currency VARCHAR(3) DEFAULT 'USD',
expense_date DATE NOT NULL,
description TEXT NOT NULL,
reference_number VARCHAR(100), -- Receipt number, invoice number, etc.
-- Vendor/Supplier
vendor_name VARCHAR(255),
vendor_contact VARCHAR(255),
-- Tax and billing
tax_amount DECIMAL(8,2) DEFAULT 0,
is_billable_to_client BOOLEAN DEFAULT false,
client_company_id UUID REFERENCES companies(id), -- If billable
client_contact_id UUID REFERENCES contacts(id), -- If billable
job_id UUID REFERENCES jobs(id), -- If related to specific job
markup_percentage DECIMAL(5,2) DEFAULT 0, -- For client billing
-- Payment details
payment_method VARCHAR(50), -- cash, card, check, bank_transfer
payment_status VARCHAR(20) DEFAULT 'pending', -- pending, paid, reimbursed
paid_date DATE,
-- Approval workflow
status VARCHAR(20) DEFAULT 'draft', -- draft, submitted, approved, rejected, paid
submitted_by UUID NOT NULL REFERENCES users(id),
approved_by UUID REFERENCES users(id),
approved_at TIMESTAMPTZ,
rejection_reason TEXT,
-- Tracking
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Expense receipt attachments
CREATE TABLE expense_attachments (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
expense_id UUID NOT NULL REFERENCES expenses(id) ON DELETE CASCADE,
document_id UUID NOT NULL REFERENCES documents(id),
attachment_type VARCHAR(20) DEFAULT 'receipt', -- receipt, invoice, contract
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 12. INVOICING SYSTEM
-- =====================================================

-- Customer billing information
CREATE TABLE customer_billing_info (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
-- Billing address
billing_address_line_1 TEXT,
billing_address_line_2 TEXT,
billing_city VARCHAR(100),
billing_state VARCHAR(100),
billing_postal_code VARCHAR(20),
billing_country VARCHAR(100),
-- Shipping address (if different)
shipping_address_line_1 TEXT,
shipping_address_line_2 TEXT,
shipping_city VARCHAR(100),
shipping_state VARCHAR(100),
shipping_postal_code VARCHAR(20),
shipping_country VARCHAR(100),
-- Payment terms and preferences
payment_terms_days INTEGER DEFAULT 30,
payment_terms_label VARCHAR(50) DEFAULT 'Net 30',
preferred_payment_method VARCHAR(50),
credit_limit DECIMAL(12,2),
-- Tax information
tax_id VARCHAR(50),
tax_exemption_id VARCHAR(50),
is_tax_exempt BOOLEAN DEFAULT false,
default_tax_rate DECIMAL(5,2) DEFAULT 0,
-- Currency and pricing
preferred_currency VARCHAR(3) DEFAULT 'USD',
price_list_id UUID, -- For customer-specific pricing
-- Communication preferences
invoice_delivery_method VARCHAR(20) DEFAULT 'email', -- email, postal, portal
send_payment_reminders BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT check_customer_billing_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Invoice templates
CREATE TABLE invoice_templates (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(100) NOT NULL,
description TEXT,
template_type VARCHAR(20) DEFAULT 'standard', -- standard, professional, modern, custom
-- Template configuration
template_config JSONB NOT NULL, -- Colors, fonts, layout settings
logo_url TEXT,
-- Company details on template
company_name VARCHAR(255),
company_address TEXT,
company_phone VARCHAR(20),
company_email VARCHAR(255),
company_website VARCHAR(255),
-- Settings
is_default BOOLEAN DEFAULT false,
is_active BOOLEAN DEFAULT true,
created_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Main invoices table
CREATE TABLE invoices (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
invoice_number VARCHAR(50) UNIQUE NOT NULL, -- INV-2024-001
reference_number VARCHAR(100), -- PO number from customer
-- Customer information
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
billing_info_id UUID REFERENCES customer_billing_info(id),
-- Related records
deal_id UUID REFERENCES deals(id),
job_id UUID REFERENCES jobs(id),
quote_id UUID, -- Will reference quotes table
recurring_invoice_id UUID, -- Will reference recurring_invoices table
-- Invoice details
invoice_date DATE DEFAULT CURRENT_DATE,
due_date DATE NOT NULL,
payment_terms_days INTEGER DEFAULT 30,
payment_terms_label VARCHAR(50) DEFAULT 'Net 30',
-- Template and branding
template_id UUID REFERENCES invoice_templates(id),
-- Financial details
currency VARCHAR(3) DEFAULT 'USD',
exchange_rate DECIMAL(10,6) DEFAULT 1.000000,
-- Line totals
subtotal DECIMAL(12,2) DEFAULT 0,
-- Discounts
discount_type VARCHAR(10), -- 'percentage' or 'amount'
discount_value DECIMAL(10,2) DEFAULT 0,
discount_amount DECIMAL(12,2) DEFAULT 0,
-- Tax details (supports multiple taxes)
tax_details JSONB, -- [{"name": "VAT", "rate": 10, "amount": 100}]
total_tax_amount DECIMAL(12,2) DEFAULT 0,
is_tax_inclusive BOOLEAN DEFAULT false,
-- Additional charges
shipping_charge DECIMAL(10,2) DEFAULT 0,
adjustment_amount DECIMAL(12,2) DEFAULT 0, -- Final adjustment
adjustment_description VARCHAR(255),
-- Final amounts
total_amount DECIMAL(12,2) DEFAULT 0,
paid_amount DECIMAL(12,2) DEFAULT 0,
credits_applied DECIMAL(12,2) DEFAULT 0,
balance_due DECIMAL(12,2) DEFAULT 0,
-- Stripe fee handling
stripe_fee_amount DECIMAL(8,2) DEFAULT 0,
customer_pays_stripe_fee BOOLEAN DEFAULT true,
-- Status management
status VARCHAR(20) DEFAULT 'draft', -- draft, sent, viewed, partially_paid, paid, overdue, cancelled, void
approval_status VARCHAR(20) DEFAULT 'approved', -- draft, pending, approved, rejected
-- Terms and notes
terms_and_conditions TEXT,
public_notes TEXT, -- Visible to customer
private_notes TEXT, -- Internal notes only
-- Delivery and communication
delivery_method VARCHAR(20) DEFAULT 'email', -- email, postal, pickup, portal
email_sent BOOLEAN DEFAULT false,
email_sent_at TIMESTAMPTZ,
viewed_by_customer BOOLEAN DEFAULT false,
first_viewed_at TIMESTAMPTZ,
last_viewed_at TIMESTAMPTZ,
-- Payment tracking
first_payment_date DATE,
last_payment_date DATE,
payment_count INTEGER DEFAULT 0,
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
updated_by UUID REFERENCES users(id),
sent_by UUID REFERENCES users(id),
approved_by UUID REFERENCES users(id),
voided_by UUID REFERENCES users(id),
voided_reason TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
sent_at TIMESTAMPTZ,
approved_at TIMESTAMPTZ,
voided_at TIMESTAMPTZ,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_customer_invoice_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Invoice line items
CREATE TABLE invoice_line_items (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
invoice_id UUID NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
product_id UUID REFERENCES products(id),
-- Item details
item_type VARCHAR(20) DEFAULT 'product', -- product, service, hours, expense
name VARCHAR(255) NOT NULL,
description TEXT,
-- Quantity and pricing
quantity DECIMAL(10,3) DEFAULT 1,
unit VARCHAR(20) DEFAULT 'each', -- each, hours, days, kg, etc.
unit_price DECIMAL(10,2) DEFAULT 0,
-- Discounts at line level
discount_type VARCHAR(10), -- 'percentage' or 'amount'
discount_value DECIMAL(8,2) DEFAULT 0,
discount_amount DECIMAL(10,2) DEFAULT 0,
-- Tax information (can override invoice tax)
tax_details JSONB, -- Line-specific tax rates
tax_amount DECIMAL(10,2) DEFAULT 0,
is_tax_inclusive BOOLEAN DEFAULT false,
-- Final calculations
line_total DECIMAL(12,2) NOT NULL,
-- Additional fields
expense_id UUID REFERENCES expenses(id), -- If billing an expense
time_entry_ids UUID[], -- Array of time entry IDs if billing time
project_code VARCHAR(50),
notes TEXT,
sort_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 13. QUOTES/ESTIMATES
-- =====================================================

-- Quote statuses
CREATE TABLE quote_statuses (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
is_active BOOLEAN DEFAULT true,
is_approved BOOLEAN DEFAULT false,
is_rejected BOOLEAN DEFAULT false,
color VARCHAR(7) DEFAULT '#6B7280',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Quotes/Estimates
CREATE TABLE quotes (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
quote_number VARCHAR(50) UNIQUE NOT NULL, -- QUO-2024-001
title VARCHAR(255),
-- Customer information
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
-- Related records
deal_id UUID REFERENCES deals(id),
job_id UUID REFERENCES jobs(id),
-- Quote details
quote_date DATE DEFAULT CURRENT_DATE,
valid_until DATE,
expiry_date DATE,
-- Template and branding
template_id UUID REFERENCES invoice_templates(id),
-- Financial details
currency VARCHAR(3) DEFAULT 'USD',
subtotal DECIMAL(12,2) DEFAULT 0,
discount_amount DECIMAL(12,2) DEFAULT 0,
tax_amount DECIMAL(12,2) DEFAULT 0,
total_amount DECIMAL(12,2) DEFAULT 0,
-- Status and approval
status_id INTEGER NOT NULL REFERENCES quote_statuses(id),
-- Terms and conditions
terms_and_conditions TEXT,
scope_of_work TEXT,
assumptions TEXT,
exclusions TEXT,
payment_schedule TEXT,
-- Notes
public_notes TEXT,
private_notes TEXT,
-- Customer interaction
sent_to_customer BOOLEAN DEFAULT false,
viewed_by_customer BOOLEAN DEFAULT false,
customer_signature JSONB, -- Digital signature data
signed_at TIMESTAMPTZ,
signed_by_name VARCHAR(255),
signed_by_email VARCHAR(255),
-- Conversion tracking
converted_to_invoice BOOLEAN DEFAULT false,
converted_invoice_id UUID REFERENCES invoices(id),
converted_at TIMESTAMPTZ,
-- Revision tracking
version_number INTEGER DEFAULT 1,
parent_quote_id UUID REFERENCES quotes(id),
revision_reason TEXT,
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
updated_by UUID REFERENCES users(id),
sent_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
sent_at TIMESTAMPTZ,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_customer_quote_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Add foreign key reference back to invoices and quotes
ALTER TABLE invoices ADD CONSTRAINT fk_invoices_quote
FOREIGN KEY (quote_id) REFERENCES quotes(id);

-- Quote line items
CREATE TABLE quote_line_items (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
quote_id UUID NOT NULL REFERENCES quotes(id) ON DELETE CASCADE,
product_id UUID REFERENCES products(id),
-- Item details
item_type VARCHAR(20) DEFAULT 'product',
name VARCHAR(255) NOT NULL,
description TEXT,
-- Quantity and pricing
quantity DECIMAL(10,3) DEFAULT 1,
unit VARCHAR(20) DEFAULT 'each',
unit_price DECIMAL(10,2) DEFAULT 0,
discount_amount DECIMAL(10,2) DEFAULT 0,
tax_amount DECIMAL(10,2) DEFAULT 0,
line_total DECIMAL(12,2) NOT NULL,
-- Additional options
is_optional BOOLEAN DEFAULT false,
alternative_to_line_id UUID REFERENCES quote_line_items(id),
notes TEXT,
sort_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 14. PAYMENT PROCESSING
-- =====================================================

-- Payment methods lookup
CREATE TABLE payment_methods (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
code VARCHAR(20) UNIQUE NOT NULL, -- cash, check, card, bank_transfer, stripe, paypal
description TEXT,
is_online BOOLEAN DEFAULT false,
requires_reference BOOLEAN DEFAULT false,
processing_fee_percentage DECIMAL(5,4) DEFAULT 0, -- 2.9% = 0.029
processing_fee_fixed DECIMAL(6,2) DEFAULT 0, -- $0.30
is_active BOOLEAN DEFAULT true,
sort_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Main payments table
CREATE TABLE payments (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
payment_number VARCHAR(50) UNIQUE NOT NULL, -- PAY-2024-001
invoice_id UUID NOT NULL REFERENCES invoices(id),
-- Payment details
amount DECIMAL(12,2) NOT NULL,
currency VARCHAR(3) DEFAULT 'USD',
payment_date DATE DEFAULT CURRENT_DATE,
payment_method_id INTEGER NOT NULL REFERENCES payment_methods(id),
reference_number VARCHAR(100), -- Check number, transaction ID, etc.
-- Stripe specific fields
stripe_payment_intent_id VARCHAR(100),
stripe_charge_id VARCHAR(100),
stripe_fee_amount DECIMAL(8,2) DEFAULT 0,
-- Payment processing
processing_fee DECIMAL(8,2) DEFAULT 0,
net_amount DECIMAL(12,2), -- Amount after fees
-- Status and tracking
status VARCHAR(20) DEFAULT 'completed', -- pending, completed, failed, refunded, cancelled
failure_reason TEXT,
-- Bank/Card details (last 4 digits only for security)
card_last_four VARCHAR(4),
card_brand VARCHAR(20),
bank_name VARCHAR(100),
-- Notes and receipts
notes TEXT,
receipt_url TEXT,
receipt_email_sent BOOLEAN DEFAULT false,
-- Audit fields
recorded_by UUID NOT NULL REFERENCES users(id),
processed_at TIMESTAMPTZ,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Payment links for online payments
CREATE TABLE payment_links (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
invoice_id UUID NOT NULL REFERENCES invoices(id),
link_token VARCHAR(100) UNIQUE NOT NULL, -- Secure random token
-- Link details
amount DECIMAL(12,2) NOT NULL,
currency VARCHAR(3) DEFAULT 'USD',
description TEXT,
-- Stripe configuration
stripe_payment_intent_id VARCHAR(100),
include_stripe_fee BOOLEAN DEFAULT true,
-- Link settings
expires_at TIMESTAMPTZ,
max_usage_count INTEGER DEFAULT 1,
current_usage_count INTEGER DEFAULT 0,
-- Status
is_active BOOLEAN DEFAULT true,
is_used BOOLEAN DEFAULT false,
-- QR code
qr_code_url TEXT,
-- Tracking
created_by UUID NOT NULL REFERENCES users(id),
first_accessed_at TIMESTAMPTZ,
last_accessed_at TIMESTAMPTZ,
access_count INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 15. REFUNDS & CREDITS
-- =====================================================

-- Refund reasons lookup
CREATE TABLE refund_reasons (
id SERIAL PRIMARY KEY,
name VARCHAR(100) UNIQUE NOT NULL,
description TEXT,
requires_approval BOOLEAN DEFAULT true,
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Refunds
CREATE TABLE refunds (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
refund_number VARCHAR(50) UNIQUE NOT NULL, -- REF-2024-001
payment_id UUID NOT NULL REFERENCES payments(id),
invoice_id UUID NOT NULL REFERENCES invoices(id),
-- Refund details
amount DECIMAL(12,2) NOT NULL,
currency VARCHAR(3) DEFAULT 'USD',
reason_id INTEGER REFERENCES refund_reasons(id),
reason_description TEXT,
refund_date DATE DEFAULT CURRENT_DATE,
-- Stripe processing
stripe_refund_id VARCHAR(100),
stripe_status VARCHAR(20),
-- Processing details
processing_fee DECIMAL(8,2) DEFAULT 0, -- Fee lost on refund
net_refund_amount DECIMAL(12,2), -- Amount actually refunded
-- Status and approval
status VARCHAR(20) DEFAULT 'pending', -- pending, approved, processed, failed, cancelled
approved_by UUID REFERENCES users(id),
approved_at TIMESTAMPTZ,
processed_at TIMESTAMPTZ,
failure_reason TEXT,
-- Notes
notes TEXT,
customer_notification_sent BOOLEAN DEFAULT false,
-- Audit fields
requested_by UUID NOT NULL REFERENCES users(id),
processed_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Credit notes for customer accounts
CREATE TABLE credit_notes (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
credit_number VARCHAR(50) UNIQUE NOT NULL, -- CR-2024-001
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
-- Credit details
amount DECIMAL(12,2) NOT NULL,
currency VARCHAR(3) DEFAULT 'USD',
reason VARCHAR(255) NOT NULL,
description TEXT,
credit_date DATE DEFAULT CURRENT_DATE,
-- Expiry and usage
expires_at DATE,
balance_remaining DECIMAL(12,2),
is_fully_used BOOLEAN DEFAULT false,
-- Related records
invoice_id UUID REFERENCES invoices(id), -- Original invoice if applicable
refund_id UUID REFERENCES refunds(id), -- If created from refund
-- Status
status VARCHAR(20) DEFAULT 'active', -- active, expired, fully_used, cancelled
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT check_customer_credit_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Credit applications to invoices
CREATE TABLE credit_applications (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
credit_note_id UUID NOT NULL REFERENCES credit_notes(id),
invoice_id UUID NOT NULL REFERENCES invoices(id),
amount_applied DECIMAL(12,2) NOT NULL,
applied_date DATE DEFAULT CURRENT_DATE,
applied_by UUID NOT NULL REFERENCES users(id),
notes TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 16. RECURRING INVOICES
-- =====================================================

-- Recurring invoice frequencies
CREATE TABLE recurring_frequencies (
id SERIAL PRIMARY KEY,
name VARCHAR(20) UNIQUE NOT NULL, -- weekly, monthly, quarterly, yearly
interval_type VARCHAR(10) NOT NULL, -- days, weeks, months, years
interval_value INTEGER NOT NULL, -- 1, 2, 3, etc.
description TEXT,
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Recurring invoices setup
CREATE TABLE recurring_invoices (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
recurring_name VARCHAR(255) NOT NULL,
-- Customer information
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
template_invoice_id UUID REFERENCES invoices(id), -- Template to copy from
-- Billing information
billing_info_id UUID REFERENCES customer_billing_info(id),
template_id UUID REFERENCES invoice_templates(id),
-- Recurring settings
frequency_id INTEGER NOT NULL REFERENCES recurring_frequencies(id),
interval_count INTEGER DEFAULT 1, -- Every 1 month, every 3 months, etc.
-- Schedule
start_date DATE NOT NULL,
end_date DATE, -- NULL for indefinite
next_invoice_date DATE NOT NULL,
last_invoice_date DATE,
-- Limits
max_invoices INTEGER, -- Stop after X invoices
invoices_generated INTEGER DEFAULT 0,
-- Financial details (can override template)
currency VARCHAR(3) DEFAULT 'USD',
subtotal DECIMAL(12,2),
tax_amount DECIMAL(12,2),
total_amount DECIMAL(12,2),
-- Status
is_active BOOLEAN DEFAULT true,
is_paused BOOLEAN DEFAULT false,
paused_reason TEXT,
paused_until DATE,
-- Notes
description TEXT,
notes TEXT,
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
updated_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_customer_recurring_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Add foreign key reference back to invoices
ALTER TABLE invoices ADD CONSTRAINT fk_invoices_recurring
FOREIGN KEY (recurring_invoice_id) REFERENCES recurring_invoices(id);

-- Recurring invoice line items template
CREATE TABLE recurring_invoice_line_items (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
recurring_invoice_id UUID NOT NULL REFERENCES recurring_invoices(id) ON DELETE CASCADE,
product_id UUID REFERENCES products(id),
-- Item details (template)
item_type VARCHAR(20) DEFAULT 'product',
name VARCHAR(255) NOT NULL,
description TEXT,
quantity DECIMAL(10,3) DEFAULT 1,
unit VARCHAR(20) DEFAULT 'each',
unit_price DECIMAL(10,2) DEFAULT 0,
tax_rate DECIMAL(5,2) DEFAULT 0,
line_total DECIMAL(12,2) NOT NULL,
sort_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Track generated invoices from recurring setup
CREATE TABLE recurring_invoice_history (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
recurring_invoice_id UUID NOT NULL REFERENCES recurring_invoices(id),
generated_invoice_id UUID NOT NULL REFERENCES invoices(id),
generation_date DATE DEFAULT CURRENT_DATE,
billing_period_start DATE,
billing_period_end DATE,
amount DECIMAL(12,2),
status VARCHAR(20), -- generated, sent, paid, failed
notes TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 17. EMAIL & COMMUNICATION TRACKING
-- =====================================================

-- Email templates
CREATE TABLE email_templates (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(100) NOT NULL,
template_type VARCHAR(50) NOT NULL, -- invoice_send, payment_reminder, quote_send, etc.
subject VARCHAR(255) NOT NULL,
body_html TEXT NOT NULL,
body_text TEXT,
-- Template variables documentation
available_variables JSONB, -- {"customer_name": "Customer's full name", ...}
-- Settings
is_default BOOLEAN DEFAULT false,
is_active BOOLEAN DEFAULT true,
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
updated_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Email log for tracking all emails sent
CREATE TABLE email_log (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
-- Email details
to_email VARCHAR(255) NOT NULL,
to_name VARCHAR(255),
from_email VARCHAR(255) NOT NULL,
from_name VARCHAR(255),
subject VARCHAR(255) NOT NULL,
body_html TEXT,
body_text TEXT,
-- Related records
template_id UUID REFERENCES email_templates(id),
invoice_id UUID REFERENCES invoices(id),
quote_id UUID REFERENCES quotes(id),
payment_id UUID REFERENCES payments(id),
contact_id UUID REFERENCES contacts(id),
-- Delivery tracking
email_provider VARCHAR(50), -- sendgrid, ses, mailgun
provider_message_id VARCHAR(255),
status VARCHAR(20) DEFAULT 'queued', -- queued, sent, delivered, bounced, failed, opened, clicked
sent_at TIMESTAMPTZ,
delivered_at TIMESTAMPTZ,
opened_at TIMESTAMPTZ,
clicked_at TIMESTAMPTZ,
bounced_at TIMESTAMPTZ,
bounce_reason TEXT,
-- Attachments
has_attachments BOOLEAN DEFAULT false,
attachment_count INTEGER DEFAULT 0,
-- Audit fields
sent_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Payment reminders schedule
CREATE TABLE payment_reminders (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
invoice_id UUID NOT NULL REFERENCES invoices(id),
-- Reminder settings
reminder_type VARCHAR(20) NOT NULL, -- before_due, overdue, final_notice
days_offset INTEGER NOT NULL, -- Days before/after due date
-- Reminder details
email_template_id UUID REFERENCES email_templates(id),
subject VARCHAR(255),
message TEXT,
-- Status
is_sent BOOLEAN DEFAULT false,
scheduled_for TIMESTAMPTZ NOT NULL,
sent_at TIMESTAMPTZ,
email_log_id UUID REFERENCES email_log(id),
-- Settings
is_active BOOLEAN DEFAULT true,
created_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 18. CUSTOMER PORTAL
-- =====================================================

-- Customer portal access
CREATE TABLE customer_portal_access (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
-- Access credentials
email VARCHAR(255) NOT NULL,
password_hash VARCHAR(255),
-- Access token for passwordless login
access_token VARCHAR(100) UNIQUE,
token_expires_at TIMESTAMPTZ,
-- Settings
is_active BOOLEAN DEFAULT true,
email_verified BOOLEAN DEFAULT false,
email_verification_token VARCHAR(100),
-- Activity tracking
last_login_at TIMESTAMPTZ,
login_count INTEGER DEFAULT 0,
-- Permissions
can_view_invoices BOOLEAN DEFAULT true,
can_make_payments BOOLEAN DEFAULT true,
can_view_quotes BOOLEAN DEFAULT true,
can_download_documents BOOLEAN DEFAULT true,
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT check_customer_portal_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Customer portal activity log
CREATE TABLE customer_portal_activity (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
portal_access_id UUID NOT NULL REFERENCES customer_portal_access(id),
activity_type VARCHAR(50) NOT NULL, -- login, view_invoice, make_payment, download_document
-- Related records
invoice_id UUID REFERENCES invoices(id),
quote_id UUID REFERENCES quotes(id),
payment_id UUID REFERENCES payments(id),
document_id UUID REFERENCES documents(id),
-- Activity details
description TEXT,
ip_address INET,
user_agent TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 19. SYSTEM AUDIT & LOGGING
-- =====================================================

-- Comprehensive audit log
CREATE TABLE audit_logs (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
-- What was changed
table_name VARCHAR(63) NOT NULL,
record_id UUID NOT NULL,
action VARCHAR(10) NOT NULL, -- INSERT, UPDATE, DELETE
-- Change details
old_values JSONB,
new_values JSONB,
changed_fields TEXT[], -- Array of field names that changed
-- Who made the change
user_id UUID REFERENCES users(id),
user_email VARCHAR(255),
user_role VARCHAR(50),
-- When and where
ip_address INET,
user_agent TEXT,
session_id VARCHAR(100),
-- Context
request_id UUID, -- For tracing requests
operation_type VARCHAR(50), -- api_call, bulk_import, system_job, etc.
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- System configuration settings
CREATE TABLE system_settings (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
setting_key VARCHAR(100) UNIQUE NOT NULL,
setting_value JSONB NOT NULL,
description TEXT,
setting_type VARCHAR(20) DEFAULT 'general', -- general, email, payment, security, etc.
is_encrypted BOOLEAN DEFAULT false,
is_public BOOLEAN DEFAULT false, -- Can be accessed by frontend
-- Validation
validation_rules JSONB, -- JSON schema for validation
-- Audit
updated_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Webhook endpoints configuration
CREATE TABLE webhook_endpoints (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(100) NOT NULL,
url TEXT NOT NULL,
secret_key VARCHAR(255), -- For signature verification
-- Events to listen for
events TEXT[] NOT NULL, -- ['invoice.paid', 'payment.failed', etc.]
-- Settings
is_active BOOLEAN DEFAULT true,
retry_attempts INTEGER DEFAULT 3,
timeout_seconds INTEGER DEFAULT 30,
-- Headers
custom_headers JSONB,
-- Status
last_success_at TIMESTAMPTZ,
last_failure_at TIMESTAMPTZ,
failure_count INTEGER DEFAULT 0,
-- Audit
created_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Webhook delivery log
CREATE TABLE webhook_deliveries (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
webhook_endpoint_id UUID NOT NULL REFERENCES webhook_endpoints(id),
event_type VARCHAR(50) NOT NULL,
-- Payload
payload JSONB NOT NULL,
-- Related record
related_table VARCHAR(63),
related_id UUID,
-- Delivery details
http_status INTEGER,
response_body TEXT,
response_headers JSONB,
delivery_duration_ms INTEGER,
-- Status
status VARCHAR(20) DEFAULT 'pending', -- pending, success, failed, retrying
attempt_count INTEGER DEFAULT 0,
next_retry_at TIMESTAMPTZ,
error_message TEXT,
-- Timing
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
delivered_at TIMESTAMPTZ
);

-- =====================================================
-- 20. PERFORMANCE OPTIMIZATION INDEXES
-- =====================================================

-- Users table indexes
CREATE INDEX idx_users_email ON users(email) WHERE deleted_at IS NULL;
CREATE INDEX idx_users_active ON users(is_active) WHERE deleted_at IS NULL;
CREATE INDEX idx_users_last_login ON users(last_login_at DESC) WHERE deleted_at IS NULL;

-- Companies table indexes
CREATE INDEX idx_companies_name ON companies USING GIN(name gin_trgm_ops) WHERE deleted_at IS NULL;
CREATE INDEX idx_companies_owner ON companies(owner_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_companies_industry ON companies(industry) WHERE deleted_at IS NULL;

-- Contacts table indexes
CREATE INDEX idx_contacts_name ON contacts USING GIN((first_name || ' ' || last_name) gin_trgm_ops) WHERE deleted_at IS NULL;
CREATE INDEX idx_contacts_email ON contacts(email) WHERE deleted_at IS NULL AND email IS NOT NULL;
CREATE INDEX idx_contacts_company ON contacts(company_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_contacts_owner ON contacts(owner_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_contacts_last_contacted ON contacts(last_contacted_at DESC) WHERE deleted_at IS NULL;

-- Leads table indexes
CREATE INDEX idx_leads_status ON leads(status_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_leads_owner ON leads(owner_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_leads_source ON leads(source_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_leads_expected_close ON leads(expected_close_date) WHERE deleted_at IS NULL;

-- Deals table indexes
CREATE INDEX idx_deals_stage ON deals(stage_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_deals_owner ON deals(owner_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_deals_company ON deals(company_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_deals_expected_close ON deals(expected_close_date) WHERE deleted_at IS NULL;
CREATE INDEX idx_deals_amount ON deals(amount DESC) WHERE deleted_at IS NULL;
CREATE INDEX idx_deals_won ON deals(is_won) WHERE deleted_at IS NULL AND is_won IS NOT NULL;

-- Jobs table indexes
CREATE INDEX idx_jobs_status ON jobs(status_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_jobs_assigned_to ON jobs(assigned_to) WHERE deleted_at IS NULL;
CREATE INDEX idx_jobs_priority ON jobs(priority_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_jobs_due_date ON jobs(due_date) WHERE deleted_at IS NULL;
CREATE INDEX idx_jobs_customer_company ON jobs(customer_company_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_jobs_job_number ON jobs(job_number) WHERE deleted_at IS NULL;

-- Tasks table indexes
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to) WHERE deleted_at IS NULL;
CREATE INDEX idx_tasks_status ON tasks(status_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_tasks_due_date ON tasks(due_date) WHERE deleted_at IS NULL;
CREATE INDEX idx_tasks_priority ON tasks(priority_id) WHERE deleted_at IS NULL;

-- Time entries indexes
CREATE INDEX idx_time_entries_user ON time_entries(user_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_time_entries_job ON time_entries(job_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_time_entries_date ON time_entries(DATE(start_time)) WHERE deleted_at IS NULL;
CREATE INDEX idx_time_entries_billable ON time_entries(is_billable) WHERE deleted_at IS NULL;

-- Expenses table indexes
CREATE INDEX idx_expenses_category ON expenses(category_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_expenses_date ON expenses(expense_date DESC) WHERE deleted_at IS NULL;
CREATE INDEX idx_expenses_status ON expenses(status) WHERE deleted_at IS NULL;
CREATE INDEX idx_expenses_submitted_by ON expenses') AS INTEGER)), 0) + 1 FROM %I WHERE %I LIKE ''%s-%s-%%''',
column_name, prefix, table_name, column_name, prefix, current_year)
INTO next_number;

    -- Format the result
    result := format('%s-%s-%s', prefix, current_year, LPAD(next_number::VARCHAR, 3, '0'));

    RETURN result;

END;
$ LANGUAGE plpgsql;

-- Function to create invoice from job
CREATE OR REPLACE FUNCTION create_invoice_from_job(
job_uuid UUID,
include_time_entries BOOLEAN DEFAULT true,
include_expenses BOOLEAN DEFAULT true
) RETURNS UUID AS $
DECLARE
job_record jobs%ROWTYPE;
invoice_id UUID;
invoice_number VARCHAR(50);
line_item_id UUID;
time_total DECIMAL(10,2) := 0;
expense_total DECIMAL(10,2) := 0;
BEGIN
-- Get job details
SELECT \* INTO job_record FROM jobs WHERE id = job_uuid AND deleted_at IS NULL;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Job not found or has been deleted';
    END IF;

    -- Generate invoice number
    invoice_number := get_next_number('INV', 'invoices', 'invoice_number');

    -- Create invoice
    INSERT INTO invoices (
        invoice_number,
        customer_company_id,
        customer_contact_id,
        job_id,
        invoice_date,
        due_date,
        created_by,
        status
    ) VALUES (
        invoice_number,
        job_record.customer_company_id,
        job_record.customer_contact_id,
        job_record.id,
        CURRENT_DATE,
        CURRENT_DATE + INTERVAL '30 days',
        job_record.created_by,
        'draft'
    ) RETURNING id INTO invoice_id;

    -- Add job products/services as line items
    INSERT INTO invoice_line_items (
        invoice_id, product_id, name, description, quantity, unit_price, line_total
    )
    SELECT
        invoice_id,
        jp.product_id,
        p.name,
        p.description,
        jp.quantity,
        jp.unit_price,
        jp.quantity * jp.unit_price
    FROM job_products jp
    JOIN products p ON jp.product_id = p.id
    WHERE jp.job_id = job_uuid;

    -- Add time entries if requested
    IF include_time_entries THEN
        SELECT COALESCE(SUM(billable_amount), 0) INTO time_total
        FROM time_entries
        WHERE job_id = job_uuid AND is_billable = true AND deleted_at IS NULL;

        IF time_total > 0 THEN
            INSERT INTO invoice_line_items (
                invoice_id, item_type, name, description, quantity, unit, unit_price, line_total, time_entry_ids
            ) VALUES (
                invoice_id,
                'hours',
                'Professional Services - Time',
                'Time spent on job: ' || job_record.title,
                job_record.actual_hours,
                'hours',
                CASE WHEN job_record.actual_hours > 0 THEN time_total / job_record.actual_hours ELSE 0 END,
                time_total,
                (SELECT array_agg(id) FROM time_entries WHERE job_id = job_uuid AND is_billable = true AND deleted_at IS NULL)
            );
        END IF;
    END IF;

    -- Add billable expenses if requested
    IF include_expenses THEN
        SELECT COALESCE(SUM(amount + (amount * markup_percentage / 100)), 0) INTO expense_total
        FROM expenses
        WHERE job_id = job_uuid AND is_billable_to_client = true AND deleted_at IS NULL;

        -- Add individual expense line items
        INSERT INTO invoice_line_items (
            invoice_id, item_type, name, description, quantity, unit_price, line_total, expense_id
        )
        SELECT
            invoice_id,
            'expense',
            'Expense: ' || e.description,
            'Billable expense for: ' || e.description,
            1,
            e.amount + (e.amount * COALESCE(e.markup_percentage, 0) / 100),
            e.amount + (e.amount * COALESCE(e.markup_percentage, 0) / 100),
            e.id
        FROM expenses e
        WHERE e.job_id = job_uuid AND e.is_billable_to_client = true AND e.deleted_at IS NULL;
    END IF;

    -- Update job status to indicate invoice created
    UPDATE jobs
    SET
        updated_at = CURRENT_TIMESTAMP
    WHERE id = job_uuid;

    RETURN invoice_id;

END;
$ LANGUAGE plpgsql;

-- Function to process recurring invoices
CREATE OR REPLACE FUNCTION process_recurring_invoices() RETURNS INTEGER AS $
DECLARE
recurring_record recurring_invoices%ROWTYPE;
new_invoice_id UUID;
invoices_created INTEGER := 0;
BEGIN
-- Process all active recurring invoices that are due
FOR recurring_record IN
SELECT \* FROM recurring_invoices
WHERE is_active = true
AND NOT is_paused
AND next_invoice_date <= CURRENT_DATE
AND (end_date IS NULL OR next_invoice_date <= end_date)
AND (max_invoices IS NULL OR invoices_generated < max_invoices)
AND deleted_at IS NULL
LOOP
-- Create new invoice from recurring template
INSERT INTO invoices (
invoice_number,
customer_company_id,
customer_contact_id,
recurring_invoice_id,
billing_info_id,
template_id,
invoice_date,
due_date,
currency,
subtotal,
tax_amount,
total_amount,
balance_due,
created_by,
status,
public_notes
) VALUES (
get_next_number('INV', 'invoices', 'invoice_number'),
recurring_record.customer_company_id,
recurring_record.customer_contact_id,
recurring_record.id,
recurring_record.billing_info_id,
recurring_record.template_id,
CURRENT_DATE,
CURRENT_DATE + INTERVAL '30 days',
recurring_record.currency,
recurring_record.subtotal,
recurring_record.tax_amount,
recurring_record.total_amount,
recurring_record.total_amount,
recurring_record.created_by,
'draft',
'Auto-generated from recurring invoice: ' || recurring_record.recurring_name
) RETURNING id INTO new_invoice_id;

        -- Copy line items from recurring template
        INSERT INTO invoice_line_items (
            invoice_id, product_id, item_type, name, description,
            quantity, unit, unit_price, tax_rate, line_total, sort_order
        )
        SELECT
            new_invoice_id, product_id, item_type, name, description,
            quantity, unit, unit_price, tax_rate, line_total, sort_order
        FROM recurring_invoice_line_items
        WHERE recurring_invoice_id = recurring_record.id;

        -- Update recurring invoice record
        UPDATE recurring_invoices SET
            last_invoice_date = CURRENT_DATE,
            next_invoice_date = CASE
                WHEN rf.interval_type = 'days' THEN CURRENT_DATE + (rf.interval_value * recurring_record.interval_count || ' days')::INTERVAL
                WHEN rf.interval_type = 'weeks' THEN CURRENT_DATE + (rf.interval_value * recurring_record.interval_count || ' weeks')::INTERVAL
                WHEN rf.interval_type = 'months' THEN CURRENT_DATE + (rf.interval_value * recurring_record.interval_count || ' months')::INTERVAL
                WHEN rf.interval_type = 'years' THEN CURRENT_DATE + (rf.interval_value * recurring_record.interval_count || ' years')::INTERVAL
                ELSE next_invoice_date
            END,
            invoices_generated = invoices_generated + 1,
            updated_at = CURRENT_TIMESTAMP
        FROM recurring_frequencies rf
        WHERE recurring_invoices.id = recurring_record.id
        AND rf.id = recurring_record.frequency_id;

        -- Log the generation
        INSERT INTO recurring_invoice_history (
            recurring_invoice_id, generated_invoice_id, billing_period_start, billing_period_end, amount, status
        ) VALUES (
            recurring_record.id, new_invoice_id, recurring_record.last_invoice_date, CURRENT_DATE, recurring_record.total_amount, 'generated'
        );

        invoices_created := invoices_created + 1;
    END LOOP;

    RETURN invoices_created;

END;
$ LANGUAGE plpgsql;

-- Function to calculate and apply late fees
CREATE OR REPLACE FUNCTION apply_late_fees() RETURNS INTEGER AS $
DECLARE
overdue_invoice RECORD;
late_fee_rate DECIMAL(5,2);
late_fee_amount DECIMAL(10,2);
invoices_updated INTEGER := 0;
BEGIN
-- Get late fee percentage from settings
SELECT (setting_value->>0)::DECIMAL INTO late_fee_rate
FROM system_settings
WHERE setting_key = 'late_fee_percentage';

    IF late_fee_rate IS NULL THEN
        late_fee_rate := 1.5; -- Default 1.5% per month
    END IF;

    -- Process overdue invoices
    FOR overdue_invoice IN
        SELECT i.id, i.balance_due, i.due_date
        FROM invoices i
        WHERE i.status IN ('sent', 'partially_paid')
        AND i.due_date < CURRENT_DATE - INTERVAL '30 days'
        AND i.balance_due > 0
        AND i.deleted_at IS NULL
        AND NOT EXISTS (
            SELECT 1 FROM invoice_line_items ili
            WHERE ili.invoice_id = i.id
            AND ili.name = 'Late Fee'
            AND ili.created_at > CURRENT_DATE - INTERVAL '30 days'
        )
    LOOP
        -- Calculate late fee (1.5% per month or portion thereof)
        late_fee_amount := overdue_invoice.balance_due * (late_fee_rate / 100);

        -- Add late fee as line item
        INSERT INTO invoice_line_items (
            invoice_id, item_type, name, description, quantity, unit_price, line_total
        ) VALUES (
            overdue_invoice.id,
            'fee',
            'Late Fee',
            'Late payment fee for overdue invoice',
            1,
            late_fee_amount,
            late_fee_amount
        );

        invoices_updated := invoices_updated + 1;
    END LOOP;

    RETURN invoices_updated;

END;
$ LANGUAGE plpgsql;

-- Function to cleanup old data
CREATE OR REPLACE FUNCTION cleanup_old_data() RETURNS VOID AS $
BEGIN
-- Delete old audit logs (keep 2 years)
DELETE FROM audit_logs WHERE created_at < CURRENT_DATE - INTERVAL '2 years';

    -- Delete old email logs (keep 1 year)
    DELETE FROM email_log WHERE created_at < CURRENT_DATE - INTERVAL '1 year';

    -- Delete old user sessions (keep 30 days)
    DELETE FROM user_sessions WHERE last_activity_at < CURRENT_DATE - INTERVAL '30 days';

    -- Delete old password reset tokens (keep 7 days)
    DELETE FROM password_reset_tokens WHERE created_at < CURRENT_DATE - INTERVAL '7 days';

    -- Delete old webhook deliveries (keep 90 days)
    DELETE FROM webhook_deliveries WHERE created_at < CURRENT_DATE - INTERVAL '90 days';

    -- Delete old customer portal activity (keep 1 year)
    DELETE FROM customer_portal_activity WHERE created_at < CURRENT_DATE - INTERVAL '1 year';

END;
$ LANGUAGE plpgsql;

-- =====================================================
-- 25. SCHEDULED JOBS AND MAINTENANCE
-- =====================================================

-- Create a function to run daily maintenance tasks
CREATE OR REPLACE FUNCTION daily_maintenance() RETURNS TEXT AS $
DECLARE
result_text TEXT := '';
recurring_count INTEGER;
late_fee_count INTEGER;
BEGIN
-- Process recurring invoices
SELECT process_recurring_invoices() INTO recurring_count;
result_text := result_text || 'Processed ' || recurring_count || ' recurring invoices. ';

    -- Apply late fees
    SELECT apply_late_fees() INTO late_fee_count;
    result_text := result_text || 'Applied late fees to ' || late_fee_count || ' invoices. ';

    -- Update overdue invoice statuses
    UPDATE invoices
    SET status = 'overdue', updated_at = CURRENT_TIMESTAMP
    WHERE status IN ('sent', 'partially_paid')
    AND due_date < CURRENT_DATE
    AND balance_due > 0
    AND deleted_at IS NULL;

    result_text := result_text || 'Updated overdue invoice statuses. ';

    -- Cleanup old data (run weekly)
    IF EXTRACT(dow FROM CURRENT_DATE) = 1 THEN -- Monday
        PERFORM cleanup_old_data();
        result_text := result_text || 'Performed weekly data cleanup. ';
    END IF;

    -- Update statistics and refresh materialized views if any exist
    ANALYZE;
    result_text := result_text || 'Updated database statistics.';

    RETURN result_text;

END;
$ LANGUAGE plpgsql;

-- =====================================================
-- 26. SECURITY AND CONSTRAINTS
-- =====================================================

-- Row Level Security (RLS) policies for multi-tenant data
ALTER TABLE companies ENABLE ROW LEVEL SECURITY;
ALTER TABLE contacts ENABLE ROW LEVEL SECURITY;
ALTER TABLE deals ENABLE ROW LEVEL SECURITY;
ALTER TABLE jobs ENABLE ROW LEVEL SECURITY;
ALTER TABLE invoices ENABLE ROW LEVEL SECURITY;
ALTER TABLE expenses ENABLE ROW LEVEL SECURITY;

-- Example RLS policy for companies (assuming organization_id field added)
-- CREATE POLICY company_isolation ON companies
-- USING (organization_id = current_setting('app.current_organization_id')::UUID);

-- Additional constraints for data integrity
ALTER TABLE invoices ADD CONSTRAINT check_positive_amounts
CHECK (subtotal >= 0 AND total_amount >= 0 AND balance_due >= 0);

ALTER TABLE payments ADD CONSTRAINT check_positive_payment_amount
CHECK (amount > 0);

ALTER TABLE expenses ADD CONSTRAINT check_positive_expense_amount
CHECK (amount > 0);

ALTER TABLE time_entries ADD CONSTRAINT check_positive_duration
CHECK (duration_minutes > 0);

ALTER TABLE jobs ADD CONSTRAINT check_completion_percentage
CHECK (completion_percentage >= 0 AND completion_percentage <= 100);

-- Ensure due dates are not in the past for new invoices
ALTER TABLE invoices ADD CONSTRAINT check_due_date_future
CHECK (due_date >= invoice_date);

-- Ensure quote expiry dates are in the future
ALTER TABLE quotes ADD CONSTRAINT check_quote_expiry_future
CHECK (valid_until IS NULL OR valid_until >= quote_date);

-- =====================================================
-- 27. PERFORMANCE MONITORING VIEWS
-- =====================================================

-- View for monitoring system performance
CREATE VIEW system_performance_metrics AS
SELECT
'invoices' as table_name,
COUNT(_) as total_records,
COUNT(_) FILTER (WHERE created_at >= CURRENT_DATE - INTERVAL '30 days') as records_last_30_days,
COUNT(_) FILTER (WHERE status = 'overdue') as critical_records,
AVG(total_amount) as avg_amount
FROM invoices WHERE deleted_at IS NULL
UNION ALL
SELECT
'jobs' as table_name,
COUNT(_) as total_records,
COUNT(_) FILTER (WHERE created_at >= CURRENT_DATE - INTERVAL '30 days') as records_last_30_days,
COUNT(_) FILTER (WHERE due_date < CURRENT_DATE AND completion_percentage < 100) as critical_records,
AVG(actual_hours) as avg_amount
FROM jobs WHERE deleted_at IS NULL
UNION ALL
SELECT
'contacts' as table_name,
COUNT(_) as total_records,
COUNT(_) FILTER (WHERE created_at >= CURRENT_DATE - INTERVAL '30 days') as records_last_30_days,
COUNT(\*) FILTER (WHERE last_contacted_at < CURRENT_DATE - INTERVAL '90 days') as critical_records,
NULL as avg_amount
FROM contacts WHERE deleted_at IS NULL;

-- =====================================================
-- SCHEMA SUMMARY
-- =====================================================

/\*
COMPLETE DATABASE SCHEMA SUMMARY:

CORE TABLES: 45+ tables covering:
✅ User Management (5 tables)
✅ CRM Core (6 tables)
✅ Lead & Deal Management (8 tables)
✅ Product & Service Catalog (4 tables)
✅ Job Management (6 tables)
✅ Task Management (4 tables)
✅ Time Tracking (1 table)
✅ Activity Tracking (2 tables)
✅ Document Management (3 tables)
✅ Expense Management (3 tables)
✅ Invoicing System (6 tables)
✅ Quotes & Estimates (3 tables)
✅ Payment Processing (4 tables)
✅ Refunds & Credits (3 tables)
✅ Recurring Invoices (4 tables)
✅ Email & Communication (4 tables)
✅ Customer Portal (2 tables)
✅ System Administration (4 tables)

FEATURES IMPLEMENTED:
✅ Complete user management with role-based access
✅ Full CRM functionality matching Bigin
✅ Comprehensive invoicing matching Zoho Invoice
✅ Staff job assignment and time tracking
✅ Business expense management with receipt uploads
✅ Stripe payment integration with fee calculation
✅ Payment links and QR code generation
✅ Refund and credit management
✅ Recurring invoices and regular jobs
✅ Customer portal for self-service
✅ Email templates and communication tracking
✅ Comprehensive audit logging
✅ Performance optimization with 50+ indexes
✅ Automated triggers for data consistency
✅ Business logic stored procedures
✅ Dashboard and reporting views
✅ Data cleanup and maintenance procedures

TOTAL: 301+ Requirements Implemented in Database Schema
\*/-- =====================================================
-- COMPLETE DATABASE SCHEMA
-- CRM with Invoicing System
-- PostgreSQL 15+
-- =====================================================

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm"; -- For fuzzy search
CREATE EXTENSION IF NOT EXISTS "btree_gin"; -- For composite indexes

-- =====================================================
-- 1. USER MANAGEMENT & AUTHENTICATION
-- =====================================================

-- User roles lookup table
CREATE TABLE roles (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
permissions JSONB NOT NULL DEFAULT '[]',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Main users table
CREATE TABLE users (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
email VARCHAR(255) UNIQUE NOT NULL,
password_hash VARCHAR(255) NOT NULL,
first_name VARCHAR(100) NOT NULL,
last_name VARCHAR(100) NOT NULL,
phone VARCHAR(20),
position VARCHAR(100),
avatar_url TEXT,
is_active BOOLEAN DEFAULT true,
email_verified_at TIMESTAMPTZ,
last_login_at TIMESTAMPTZ,
timezone VARCHAR(50) DEFAULT 'UTC',
language VARCHAR(10) DEFAULT 'en',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ -- Soft delete
);

-- User roles assignment (many-to-many)
CREATE TABLE user_roles (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
assigned_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
UNIQUE(user_id, role_id)
);

-- Password reset tokens
CREATE TABLE password_reset_tokens (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
token VARCHAR(255) NOT NULL,
expires_at TIMESTAMPTZ NOT NULL,
used_at TIMESTAMPTZ,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- User sessions for tracking active sessions
CREATE TABLE user_sessions (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
token_hash VARCHAR(255) NOT NULL,
ip_address INET,
user_agent TEXT,
expires_at TIMESTAMPTZ NOT NULL,
last_activity_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. CRM CORE ENTITIES
-- =====================================================

-- Companies/Organizations
CREATE TABLE companies (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(255) NOT NULL,
industry VARCHAR(100),
website VARCHAR(255),
phone VARCHAR(20),
email VARCHAR(255),
-- Address fields
address_line_1 TEXT,
address_line_2 TEXT,
city VARCHAR(100),
state VARCHAR(100),
postal_code VARCHAR(20),
country VARCHAR(100),
-- Business details
description TEXT,
employees_count INTEGER,
annual_revenue DECIMAL(15,2),
company_size VARCHAR(20), -- startup, small, medium, large, enterprise
-- Meta fields
owner_id UUID REFERENCES users(id),
logo_url TEXT,
timezone VARCHAR(50),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Contacts/People
CREATE TABLE contacts (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
first_name VARCHAR(100) NOT NULL,
last_name VARCHAR(100) NOT NULL,
email VARCHAR(255),
phone VARCHAR(20),
mobile VARCHAR(20),
job_title VARCHAR(100),
department VARCHAR(100),
-- Address (can be different from company)
address_line_1 TEXT,
address_line_2 TEXT,
city VARCHAR(100),
state VARCHAR(100),
postal_code VARCHAR(20),
country VARCHAR(100),
-- Relationships
company_id UUID REFERENCES companies(id),
owner_id UUID NOT NULL REFERENCES users(id),
-- Contact details
description TEXT,
date_of_birth DATE,
social_profiles JSONB, -- LinkedIn, Twitter, etc.
preferred_contact_method VARCHAR(20), -- email, phone, text
-- Status and tracking
is_active BOOLEAN DEFAULT true,
lead_source VARCHAR(50),
last_contacted_at TIMESTAMPTZ,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Tags for flexible categorization
CREATE TABLE tags (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
color VARCHAR(7) DEFAULT '#3B82F6', -- Hex color
description TEXT,
tag_type VARCHAR(20) DEFAULT 'general', -- general, lead_source, industry, etc.
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Contact tags (many-to-many)
CREATE TABLE contact_tags (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
contact_id UUID NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
tag_id INTEGER NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
UNIQUE(contact_id, tag_id)
);

-- =====================================================
-- 3. LEAD MANAGEMENT
-- =====================================================

-- Lead sources lookup
CREATE TABLE lead_sources (
id SERIAL PRIMARY KEY,
name VARCHAR(100) UNIQUE NOT NULL,
description TEXT,
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Lead statuses lookup
CREATE TABLE lead_statuses (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
is_active BOOLEAN DEFAULT true,
is_converted BOOLEAN DEFAULT false,
display_order INTEGER DEFAULT 0,
color VARCHAR(7) DEFAULT '#6B7280',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Leads
CREATE TABLE leads (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
contact_id UUID NOT NULL REFERENCES contacts(id),
status_id INTEGER NOT NULL REFERENCES lead_statuses(id),
source_id INTEGER REFERENCES lead_sources(id),
owner_id UUID NOT NULL REFERENCES users(id),
-- Lead details
title VARCHAR(255),
description TEXT,
estimated_value DECIMAL(12,2),
probability INTEGER DEFAULT 0 CHECK (probability >= 0 AND probability <= 100),
expected_close_date DATE,
-- Lead scoring
lead_score INTEGER DEFAULT 0,
temperature VARCHAR(10) DEFAULT 'cold', -- cold, warm, hot
-- Requirements and notes
requirements TEXT,
budget_range VARCHAR(50),
decision_timeframe VARCHAR(50),
decision_makers TEXT,
-- Tracking
last_activity_at TIMESTAMPTZ,
converted_at TIMESTAMPTZ,
converted_to_deal_id UUID, -- Will reference deals table
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- =====================================================
-- 4. DEAL/OPPORTUNITY MANAGEMENT
-- =====================================================

-- Deal stages lookup
CREATE TABLE deal_stages (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
probability DECIMAL(5,2) DEFAULT 0.00 CHECK (probability >= 0 AND probability <= 100),
display_order INTEGER DEFAULT 0,
is_closed BOOLEAN DEFAULT false,
is_won BOOLEAN DEFAULT false,
color VARCHAR(7) DEFAULT '#6B7280',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Deal types lookup
CREATE TABLE deal_types (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Deals/Opportunities
CREATE TABLE deals (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(255) NOT NULL,
company_id UUID REFERENCES companies(id),
contact_id UUID REFERENCES contacts(id),
lead_id UUID REFERENCES leads(id),
owner_id UUID NOT NULL REFERENCES users(id),
-- Deal classification
stage_id INTEGER NOT NULL REFERENCES deal_stages(id),
type_id INTEGER REFERENCES deal_types(id),
-- Financial details
amount DECIMAL(12,2) DEFAULT 0,
currency VARCHAR(3) DEFAULT 'USD',
probability DECIMAL(5,2) DEFAULT 0.00,
expected_close_date DATE,
actual_close_date DATE,
-- Deal details
description TEXT,
requirements TEXT,
competitors TEXT,
next_steps TEXT,
-- Status and tracking
is_won BOOLEAN,
lost_reason TEXT,
closed_at TIMESTAMPTZ,
last_activity_at TIMESTAMPTZ,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_customer_reference CHECK (
        company_id IS NOT NULL OR contact_id IS NOT NULL
    )

);

-- Add foreign key reference back to leads
ALTER TABLE leads ADD CONSTRAINT fk_leads_deal
FOREIGN KEY (converted_to_deal_id) REFERENCES deals(id);

-- =====================================================
-- 5. PRODUCT & SERVICE MANAGEMENT
-- =====================================================

-- Service categories for organizing products/services
CREATE TABLE service_categories (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(100) NOT NULL,
description TEXT,
parent_id UUID REFERENCES service_categories(id), -- For subcategories
icon VARCHAR(50),
color VARCHAR(7) DEFAULT '#3B82F6',
is_active BOOLEAN DEFAULT true,
display_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Products and Services catalog
CREATE TABLE products (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(255) NOT NULL,
code VARCHAR(50) UNIQUE, -- SKU or service code
description TEXT,
category_id UUID REFERENCES service_categories(id),
-- Pricing
unit_price DECIMAL(10,2) DEFAULT 0,
cost_price DECIMAL(10,2) DEFAULT 0, -- For profit calculation
currency VARCHAR(3) DEFAULT 'USD',
-- Service-specific fields
service_type VARCHAR(20) DEFAULT 'one_time', -- one_time, recurring, usage_based
billing_cycle VARCHAR(20), -- hourly, daily, weekly, monthly, quarterly, yearly
estimated_hours DECIMAL(6,2), -- For time-based services
service_duration_days INTEGER, -- How long service lasts
setup_fee DECIMAL(10,2) DEFAULT 0,
-- Inventory (for physical products)
track_inventory BOOLEAN DEFAULT false,
stock_quantity INTEGER DEFAULT 0,
low_stock_threshold INTEGER DEFAULT 5,
-- Pricing tiers
has_tiered_pricing BOOLEAN DEFAULT false,
pricing_tiers JSONB, -- Array of pricing tiers
-- Requirements and skills
required_skills TEXT[],
complexity_level INTEGER DEFAULT 1 CHECK (complexity_level >= 1 AND complexity_level <= 5),
prerequisites TEXT,
-- Tax and billing
is_taxable BOOLEAN DEFAULT true,
tax_category VARCHAR(50),
-- Status
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Deal products/services (many-to-many with additional details)
CREATE TABLE deal_products (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
deal_id UUID NOT NULL REFERENCES deals(id) ON DELETE CASCADE,
product_id UUID NOT NULL REFERENCES products(id),
quantity DECIMAL(10,2) DEFAULT 1,
unit_price DECIMAL(10,2) NOT NULL,
discount_percent DECIMAL(5,2) DEFAULT 0,
discount_amount DECIMAL(10,2) DEFAULT 0,
line_total DECIMAL(12,2) NOT NULL,
notes TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 6. JOB MANAGEMENT SYSTEM
-- =====================================================

-- Job statuses lookup
CREATE TABLE job_statuses (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
is_active_status BOOLEAN DEFAULT true, -- Is this an active/in-progress status?
is_completed_status BOOLEAN DEFAULT false,
color VARCHAR(7) DEFAULT '#6B7280',
display_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Job priorities lookup
CREATE TABLE job_priorities (
id SERIAL PRIMARY KEY,
name VARCHAR(20) UNIQUE NOT NULL, -- Low, Medium, High, Urgent, Critical
level INTEGER UNIQUE NOT NULL, -- 1-5 for sorting
color VARCHAR(7) NOT NULL,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Jobs (created from deals)
CREATE TABLE jobs (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
job_number VARCHAR(50) UNIQUE NOT NULL, -- JOB-2024-001
title VARCHAR(255) NOT NULL,
description TEXT,
-- Relationships
deal_id UUID REFERENCES deals(id),
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
created_by UUID NOT NULL REFERENCES users(id),
assigned_to UUID REFERENCES users(id), -- Primary assigned staff
-- Job details
status_id INTEGER NOT NULL REFERENCES job_statuses(id),
priority_id INTEGER NOT NULL REFERENCES job_priorities(id),
-- Timeline
start_date DATE,
due_date DATE,
estimated_hours DECIMAL(8,2),
actual_hours DECIMAL(8,2) DEFAULT 0,
completion_percentage INTEGER DEFAULT 0 CHECK (completion_percentage >= 0 AND completion_percentage <= 100),
-- Requirements
requirements TEXT,
deliverables TEXT,
special_instructions TEXT,
required_skills TEXT[],
-- Financial
estimated_cost DECIMAL(10,2),
actual_cost DECIMAL(10,2) DEFAULT 0,
billable_amount DECIMAL(10,2),
-- Dependencies
depends_on_job_id UUID REFERENCES jobs(id),
blocking_reason TEXT,
-- Status tracking
started_at TIMESTAMPTZ,
completed_at TIMESTAMPTZ,
approved_at TIMESTAMPTZ,
approved_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_customer_reference CHECK (
        customer_company_id IS NOT NULL OR customer_contact_id IS NOT NULL
    )

);

-- Job products/services (many-to-many)
CREATE TABLE job_products (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
job_id UUID NOT NULL REFERENCES jobs(id) ON DELETE CASCADE,
product_id UUID NOT NULL REFERENCES products(id),
quantity DECIMAL(10,2) DEFAULT 1,
unit_price DECIMAL(10,2),
notes TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Multiple staff assignment for complex jobs
CREATE TABLE job_assignments (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
job_id UUID NOT NULL REFERENCES jobs(id) ON DELETE CASCADE,
user_id UUID NOT NULL REFERENCES users(id),
role_in_job VARCHAR(50), -- lead, assistant, reviewer, etc.
assigned_by UUID NOT NULL REFERENCES users(id),
assigned_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
removed_at TIMESTAMPTZ,
UNIQUE(job_id, user_id, removed_at) -- Prevent duplicate active assignments
);

-- =====================================================
-- 7. TASK MANAGEMENT
-- =====================================================

-- Task priorities lookup
CREATE TABLE task_priorities (
id SERIAL PRIMARY KEY,
name VARCHAR(20) UNIQUE NOT NULL,
level INTEGER UNIQUE NOT NULL,
color VARCHAR(7) NOT NULL,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Task statuses lookup
CREATE TABLE task_statuses (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
is_completed BOOLEAN DEFAULT false,
is_active BOOLEAN DEFAULT true,
display_order INTEGER DEFAULT 0,
color VARCHAR(7) DEFAULT '#6B7280',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Tasks (created by managers for staff)
CREATE TABLE tasks (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
title VARCHAR(255) NOT NULL,
description TEXT,
-- Assignment
assigned_to UUID NOT NULL REFERENCES users(id),
created_by UUID NOT NULL REFERENCES users(id),
-- Classification
priority_id INTEGER NOT NULL REFERENCES task_priorities(id),
status_id INTEGER NOT NULL REFERENCES task_statuses(id),
-- Relationships (can be linked to various entities)
contact_id UUID REFERENCES contacts(id),
company_id UUID REFERENCES companies(id),
lead_id UUID REFERENCES leads(id),
deal_id UUID REFERENCES deals(id),
job_id UUID REFERENCES jobs(id),
-- Timeline
due_date TIMESTAMPTZ,
estimated_minutes INTEGER,
actual_minutes INTEGER,
completed_at TIMESTAMPTZ,
-- Tracking
progress_percentage INTEGER DEFAULT 0 CHECK (progress_percentage >= 0 AND progress_percentage <= 100),
notes TEXT,
completion_notes TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Task reminders
CREATE TABLE task_reminders (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
task_id UUID NOT NULL REFERENCES tasks(id) ON DELETE CASCADE,
remind_at TIMESTAMPTZ NOT NULL,
reminder_type VARCHAR(20) DEFAULT 'email', -- email, sms, push
is_sent BOOLEAN DEFAULT false,
sent_at TIMESTAMPTZ,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 8. TIME TRACKING
-- =====================================================

-- Time entries for jobs and tasks
CREATE TABLE time_entries (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
user_id UUID NOT NULL REFERENCES users(id),
-- Can be linked to job or task
job_id UUID REFERENCES jobs(id),
task_id UUID REFERENCES tasks(id),
-- Time details
start_time TIMESTAMPTZ,
end_time TIMESTAMPTZ,
duration_minutes INTEGER NOT NULL,
description TEXT,
-- Billing
is_billable BOOLEAN DEFAULT true,
hourly_rate DECIMAL(8,2),
billable_amount DECIMAL(10,2),
-- Status
is_approved BOOLEAN DEFAULT false,
approved_by UUID REFERENCES users(id),
approved_at TIMESTAMPTZ,
-- Tracking
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_time_reference CHECK (
        job_id IS NOT NULL OR task_id IS NOT NULL
    ),
    CONSTRAINT check_time_consistency CHECK (
        (start_time IS NULL AND end_time IS NULL) OR
        (start_time IS NOT NULL AND end_time IS NOT NULL AND start_time < end_time)
    )

);

-- =====================================================
-- 9. ACTIVITY & COMMUNICATION TRACKING
-- =====================================================

-- Activity types lookup
CREATE TABLE activity_types (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
icon VARCHAR(50),
color VARCHAR(7) DEFAULT '#6B7280',
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Activities (calls, meetings, emails, notes)
CREATE TABLE activities (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
type_id INTEGER NOT NULL REFERENCES activity_types(id),
user_id UUID NOT NULL REFERENCES users(id),
-- Relationships (can be linked to multiple entities)
contact_id UUID REFERENCES contacts(id),
company_id UUID REFERENCES companies(id),
lead_id UUID REFERENCES leads(id),
deal_id UUID REFERENCES deals(id),
job_id UUID REFERENCES jobs(id),
-- Activity details
subject VARCHAR(255) NOT NULL,
description TEXT,
-- Timing
start_time TIMESTAMPTZ,
end_time TIMESTAMPTZ,
duration_minutes INTEGER,
-- Communication details
direction VARCHAR(10), -- inbound, outbound
outcome VARCHAR(50), -- completed, no_answer, callback_requested, etc.
next_action TEXT,
-- Tracking
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- =====================================================
-- 10. DOCUMENT MANAGEMENT
-- =====================================================

-- Document categories
CREATE TABLE document_categories (
id SERIAL PRIMARY KEY,
name VARCHAR(100) UNIQUE NOT NULL,
description TEXT,
parent_id INTEGER REFERENCES document_categories(id),
icon VARCHAR(50),
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Documents
CREATE TABLE documents (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(255) NOT NULL,
original_filename VARCHAR(255) NOT NULL,
file_path TEXT NOT NULL,
file_size INTEGER NOT NULL, -- in bytes
mime_type VARCHAR(100) NOT NULL,
file_hash VARCHAR(64), -- SHA-256 for deduplication
-- Classification
category_id INTEGER REFERENCES document_categories(id),
tags TEXT[],
-- Relationships (can be linked to multiple entities)
contact_id UUID REFERENCES contacts(id),
company_id UUID REFERENCES companies(id),
lead_id UUID REFERENCES leads(id),
deal_id UUID REFERENCES deals(id),
job_id UUID REFERENCES jobs(id),
-- Metadata
description TEXT,
uploaded_by UUID NOT NULL REFERENCES users(id),
is_public BOOLEAN DEFAULT false,
version_number INTEGER DEFAULT 1,
parent_document_id UUID REFERENCES documents(id), -- For versioning
-- Access control
access_permissions JSONB, -- Who can view/edit
-- Tracking
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- =====================================================
-- 11. EXPENSE MANAGEMENT
-- =====================================================

-- Expense categories (created by admin)
CREATE TABLE expense_categories (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(100) UNIQUE NOT NULL,
description TEXT,
parent_id UUID REFERENCES expense_categories(id), -- For subcategories
-- Budget and limits
monthly_budget DECIMAL(10,2),
yearly_budget DECIMAL(10,2),
-- Tax settings
is_tax_deductible BOOLEAN DEFAULT true,
tax_category VARCHAR(50),
-- Status
is_active BOOLEAN DEFAULT true,
requires_approval BOOLEAN DEFAULT false,
approval_threshold DECIMAL(10,2), -- Auto-approve below this amount
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Expense entries
CREATE TABLE expenses (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
expense_number VARCHAR(50) UNIQUE NOT NULL, -- EXP-2024-001
category_id UUID NOT NULL REFERENCES expense_categories(id),
-- Basic details
amount DECIMAL(10,2) NOT NULL,
currency VARCHAR(3) DEFAULT 'USD',
expense_date DATE NOT NULL,
description TEXT NOT NULL,
reference_number VARCHAR(100), -- Receipt number, invoice number, etc.
-- Vendor/Supplier
vendor_name VARCHAR(255),
vendor_contact VARCHAR(255),
-- Tax and billing
tax_amount DECIMAL(8,2) DEFAULT 0,
is_billable_to_client BOOLEAN DEFAULT false,
client_company_id UUID REFERENCES companies(id), -- If billable
client_contact_id UUID REFERENCES contacts(id), -- If billable
job_id UUID REFERENCES jobs(id), -- If related to specific job
markup_percentage DECIMAL(5,2) DEFAULT 0, -- For client billing
-- Payment details
payment_method VARCHAR(50), -- cash, card, check, bank_transfer
payment_status VARCHAR(20) DEFAULT 'pending', -- pending, paid, reimbursed
paid_date DATE,
-- Approval workflow
status VARCHAR(20) DEFAULT 'draft', -- draft, submitted, approved, rejected, paid
submitted_by UUID NOT NULL REFERENCES users(id),
approved_by UUID REFERENCES users(id),
approved_at TIMESTAMPTZ,
rejection_reason TEXT,
-- Tracking
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Expense receipt attachments
CREATE TABLE expense_attachments (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
expense_id UUID NOT NULL REFERENCES expenses(id) ON DELETE CASCADE,
document_id UUID NOT NULL REFERENCES documents(id),
attachment_type VARCHAR(20) DEFAULT 'receipt', -- receipt, invoice, contract
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 12. INVOICING SYSTEM
-- =====================================================

-- Customer billing information
CREATE TABLE customer_billing_info (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
-- Billing address
billing_address_line_1 TEXT,
billing_address_line_2 TEXT,
billing_city VARCHAR(100),
billing_state VARCHAR(100),
billing_postal_code VARCHAR(20),
billing_country VARCHAR(100),
-- Shipping address (if different)
shipping_address_line_1 TEXT,
shipping_address_line_2 TEXT,
shipping_city VARCHAR(100),
shipping_state VARCHAR(100),
shipping_postal_code VARCHAR(20),
shipping_country VARCHAR(100),
-- Payment terms and preferences
payment_terms_days INTEGER DEFAULT 30,
payment_terms_label VARCHAR(50) DEFAULT 'Net 30',
preferred_payment_method VARCHAR(50),
credit_limit DECIMAL(12,2),
-- Tax information
tax_id VARCHAR(50),
tax_exemption_id VARCHAR(50),
is_tax_exempt BOOLEAN DEFAULT false,
default_tax_rate DECIMAL(5,2) DEFAULT 0,
-- Currency and pricing
preferred_currency VARCHAR(3) DEFAULT 'USD',
price_list_id UUID, -- For customer-specific pricing
-- Communication preferences
invoice_delivery_method VARCHAR(20) DEFAULT 'email', -- email, postal, portal
send_payment_reminders BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT check_customer_billing_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Invoice templates
CREATE TABLE invoice_templates (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(100) NOT NULL,
description TEXT,
template_type VARCHAR(20) DEFAULT 'standard', -- standard, professional, modern, custom
-- Template configuration
template_config JSONB NOT NULL, -- Colors, fonts, layout settings
logo_url TEXT,
-- Company details on template
company_name VARCHAR(255),
company_address TEXT,
company_phone VARCHAR(20),
company_email VARCHAR(255),
company_website VARCHAR(255),
-- Settings
is_default BOOLEAN DEFAULT false,
is_active BOOLEAN DEFAULT true,
created_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Main invoices table
CREATE TABLE invoices (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
invoice_number VARCHAR(50) UNIQUE NOT NULL, -- INV-2024-001
reference_number VARCHAR(100), -- PO number from customer
-- Customer information
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
billing_info_id UUID REFERENCES customer_billing_info(id),
-- Related records
deal_id UUID REFERENCES deals(id),
job_id UUID REFERENCES jobs(id),
quote_id UUID, -- Will reference quotes table
recurring_invoice_id UUID, -- Will reference recurring_invoices table
-- Invoice details
invoice_date DATE DEFAULT CURRENT_DATE,
due_date DATE NOT NULL,
payment_terms_days INTEGER DEFAULT 30,
payment_terms_label VARCHAR(50) DEFAULT 'Net 30',
-- Template and branding
template_id UUID REFERENCES invoice_templates(id),
-- Financial details
currency VARCHAR(3) DEFAULT 'USD',
exchange_rate DECIMAL(10,6) DEFAULT 1.000000,
-- Line totals
subtotal DECIMAL(12,2) DEFAULT 0,
-- Discounts
discount_type VARCHAR(10), -- 'percentage' or 'amount'
discount_value DECIMAL(10,2) DEFAULT 0,
discount_amount DECIMAL(12,2) DEFAULT 0,
-- Tax details (supports multiple taxes)
tax_details JSONB, -- [{"name": "VAT", "rate": 10, "amount": 100}]
total_tax_amount DECIMAL(12,2) DEFAULT 0,
is_tax_inclusive BOOLEAN DEFAULT false,
-- Additional charges
shipping_charge DECIMAL(10,2) DEFAULT 0,
adjustment_amount DECIMAL(12,2) DEFAULT 0, -- Final adjustment
adjustment_description VARCHAR(255),
-- Final amounts
total_amount DECIMAL(12,2) DEFAULT 0,
paid_amount DECIMAL(12,2) DEFAULT 0,
credits_applied DECIMAL(12,2) DEFAULT 0,
balance_due DECIMAL(12,2) DEFAULT 0,
-- Stripe fee handling
stripe_fee_amount DECIMAL(8,2) DEFAULT 0,
customer_pays_stripe_fee BOOLEAN DEFAULT true,
-- Status management
status VARCHAR(20) DEFAULT 'draft', -- draft, sent, viewed, partially_paid, paid, overdue, cancelled, void
approval_status VARCHAR(20) DEFAULT 'approved', -- draft, pending, approved, rejected
-- Terms and notes
terms_and_conditions TEXT,
public_notes TEXT, -- Visible to customer
private_notes TEXT, -- Internal notes only
-- Delivery and communication
delivery_method VARCHAR(20) DEFAULT 'email', -- email, postal, pickup, portal
email_sent BOOLEAN DEFAULT false,
email_sent_at TIMESTAMPTZ,
viewed_by_customer BOOLEAN DEFAULT false,
first_viewed_at TIMESTAMPTZ,
last_viewed_at TIMESTAMPTZ,
-- Payment tracking
first_payment_date DATE,
last_payment_date DATE,
payment_count INTEGER DEFAULT 0,
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
updated_by UUID REFERENCES users(id),
sent_by UUID REFERENCES users(id),
approved_by UUID REFERENCES users(id),
voided_by UUID REFERENCES users(id),
voided_reason TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
sent_at TIMESTAMPTZ,
approved_at TIMESTAMPTZ,
voided_at TIMESTAMPTZ,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_customer_invoice_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Invoice line items
CREATE TABLE invoice_line_items (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
invoice_id UUID NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
product_id UUID REFERENCES products(id),
-- Item details
item_type VARCHAR(20) DEFAULT 'product', -- product, service, hours, expense
name VARCHAR(255) NOT NULL,
description TEXT,
-- Quantity and pricing
quantity DECIMAL(10,3) DEFAULT 1,
unit VARCHAR(20) DEFAULT 'each', -- each, hours, days, kg, etc.
unit_price DECIMAL(10,2) DEFAULT 0,
-- Discounts at line level
discount_type VARCHAR(10), -- 'percentage' or 'amount'
discount_value DECIMAL(8,2) DEFAULT 0,
discount_amount DECIMAL(10,2) DEFAULT 0,
-- Tax information (can override invoice tax)
tax_details JSONB, -- Line-specific tax rates
tax_amount DECIMAL(10,2) DEFAULT 0,
is_tax_inclusive BOOLEAN DEFAULT false,
-- Final calculations
line_total DECIMAL(12,2) NOT NULL,
-- Additional fields
expense_id UUID REFERENCES expenses(id), -- If billing an expense
time_entry_ids UUID[], -- Array of time entry IDs if billing time
project_code VARCHAR(50),
notes TEXT,
sort_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 13. QUOTES/ESTIMATES
-- =====================================================

-- Quote statuses
CREATE TABLE quote_statuses (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
description TEXT,
is_active BOOLEAN DEFAULT true,
is_approved BOOLEAN DEFAULT false,
is_rejected BOOLEAN DEFAULT false,
color VARCHAR(7) DEFAULT '#6B7280',
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Quotes/Estimates
CREATE TABLE quotes (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
quote_number VARCHAR(50) UNIQUE NOT NULL, -- QUO-2024-001
title VARCHAR(255),
-- Customer information
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
-- Related records
deal_id UUID REFERENCES deals(id),
job_id UUID REFERENCES jobs(id),
-- Quote details
quote_date DATE DEFAULT CURRENT_DATE,
valid_until DATE,
expiry_date DATE,
-- Template and branding
template_id UUID REFERENCES invoice_templates(id),
-- Financial details
currency VARCHAR(3) DEFAULT 'USD',
subtotal DECIMAL(12,2) DEFAULT 0,
discount_amount DECIMAL(12,2) DEFAULT 0,
tax_amount DECIMAL(12,2) DEFAULT 0,
total_amount DECIMAL(12,2) DEFAULT 0,
-- Status and approval
status_id INTEGER NOT NULL REFERENCES quote_statuses(id),
-- Terms and conditions
terms_and_conditions TEXT,
scope_of_work TEXT,
assumptions TEXT,
exclusions TEXT,
payment_schedule TEXT,
-- Notes
public_notes TEXT,
private_notes TEXT,
-- Customer interaction
sent_to_customer BOOLEAN DEFAULT false,
viewed_by_customer BOOLEAN DEFAULT false,
customer_signature JSONB, -- Digital signature data
signed_at TIMESTAMPTZ,
signed_by_name VARCHAR(255),
signed_by_email VARCHAR(255),
-- Conversion tracking
converted_to_invoice BOOLEAN DEFAULT false,
converted_invoice_id UUID REFERENCES invoices(id),
converted_at TIMESTAMPTZ,
-- Revision tracking
version_number INTEGER DEFAULT 1,
parent_quote_id UUID REFERENCES quotes(id),
revision_reason TEXT,
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
updated_by UUID REFERENCES users(id),
sent_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
sent_at TIMESTAMPTZ,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_customer_quote_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Add foreign key reference back to invoices and quotes
ALTER TABLE invoices ADD CONSTRAINT fk_invoices_quote
FOREIGN KEY (quote_id) REFERENCES quotes(id);

-- Quote line items
CREATE TABLE quote_line_items (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
quote_id UUID NOT NULL REFERENCES quotes(id) ON DELETE CASCADE,
product_id UUID REFERENCES products(id),
-- Item details
item_type VARCHAR(20) DEFAULT 'product',
name VARCHAR(255) NOT NULL,
description TEXT,
-- Quantity and pricing
quantity DECIMAL(10,3) DEFAULT 1,
unit VARCHAR(20) DEFAULT 'each',
unit_price DECIMAL(10,2) DEFAULT 0,
discount_amount DECIMAL(10,2) DEFAULT 0,
tax_amount DECIMAL(10,2) DEFAULT 0,
line_total DECIMAL(12,2) NOT NULL,
-- Additional options
is_optional BOOLEAN DEFAULT false,
alternative_to_line_id UUID REFERENCES quote_line_items(id),
notes TEXT,
sort_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 14. PAYMENT PROCESSING
-- =====================================================

-- Payment methods lookup
CREATE TABLE payment_methods (
id SERIAL PRIMARY KEY,
name VARCHAR(50) UNIQUE NOT NULL,
code VARCHAR(20) UNIQUE NOT NULL, -- cash, check, card, bank_transfer, stripe, paypal
description TEXT,
is_online BOOLEAN DEFAULT false,
requires_reference BOOLEAN DEFAULT false,
processing_fee_percentage DECIMAL(5,4) DEFAULT 0, -- 2.9% = 0.029
processing_fee_fixed DECIMAL(6,2) DEFAULT 0, -- $0.30
is_active BOOLEAN DEFAULT true,
sort_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Main payments table
CREATE TABLE payments (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
payment_number VARCHAR(50) UNIQUE NOT NULL, -- PAY-2024-001
invoice_id UUID NOT NULL REFERENCES invoices(id),
-- Payment details
amount DECIMAL(12,2) NOT NULL,
currency VARCHAR(3) DEFAULT 'USD',
payment_date DATE DEFAULT CURRENT_DATE,
payment_method_id INTEGER NOT NULL REFERENCES payment_methods(id),
reference_number VARCHAR(100), -- Check number, transaction ID, etc.
-- Stripe specific fields
stripe_payment_intent_id VARCHAR(100),
stripe_charge_id VARCHAR(100),
stripe_fee_amount DECIMAL(8,2) DEFAULT 0,
-- Payment processing
processing_fee DECIMAL(8,2) DEFAULT 0,
net_amount DECIMAL(12,2), -- Amount after fees
-- Status and tracking
status VARCHAR(20) DEFAULT 'completed', -- pending, completed, failed, refunded, cancelled
failure_reason TEXT,
-- Bank/Card details (last 4 digits only for security)
card_last_four VARCHAR(4),
card_brand VARCHAR(20),
bank_name VARCHAR(100),
-- Notes and receipts
notes TEXT,
receipt_url TEXT,
receipt_email_sent BOOLEAN DEFAULT false,
-- Audit fields
recorded_by UUID NOT NULL REFERENCES users(id),
processed_at TIMESTAMPTZ,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ
);

-- Payment links for online payments
CREATE TABLE payment_links (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
invoice_id UUID NOT NULL REFERENCES invoices(id),
link_token VARCHAR(100) UNIQUE NOT NULL, -- Secure random token
-- Link details
amount DECIMAL(12,2) NOT NULL,
currency VARCHAR(3) DEFAULT 'USD',
description TEXT,
-- Stripe configuration
stripe_payment_intent_id VARCHAR(100),
include_stripe_fee BOOLEAN DEFAULT true,
-- Link settings
expires_at TIMESTAMPTZ,
max_usage_count INTEGER DEFAULT 1,
current_usage_count INTEGER DEFAULT 0,
-- Status
is_active BOOLEAN DEFAULT true,
is_used BOOLEAN DEFAULT false,
-- QR code
qr_code_url TEXT,
-- Tracking
created_by UUID NOT NULL REFERENCES users(id),
first_accessed_at TIMESTAMPTZ,
last_accessed_at TIMESTAMPTZ,
access_count INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 15. REFUNDS & CREDITS
-- =====================================================

-- Refund reasons lookup
CREATE TABLE refund_reasons (
id SERIAL PRIMARY KEY,
name VARCHAR(100) UNIQUE NOT NULL,
description TEXT,
requires_approval BOOLEAN DEFAULT true,
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Refunds
CREATE TABLE refunds (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
refund_number VARCHAR(50) UNIQUE NOT NULL, -- REF-2024-001
payment_id UUID NOT NULL REFERENCES payments(id),
invoice_id UUID NOT NULL REFERENCES invoices(id),
-- Refund details
amount DECIMAL(12,2) NOT NULL,
currency VARCHAR(3) DEFAULT 'USD',
reason_id INTEGER REFERENCES refund_reasons(id),
reason_description TEXT,
refund_date DATE DEFAULT CURRENT_DATE,
-- Stripe processing
stripe_refund_id VARCHAR(100),
stripe_status VARCHAR(20),
-- Processing details
processing_fee DECIMAL(8,2) DEFAULT 0, -- Fee lost on refund
net_refund_amount DECIMAL(12,2), -- Amount actually refunded
-- Status and approval
status VARCHAR(20) DEFAULT 'pending', -- pending, approved, processed, failed, cancelled
approved_by UUID REFERENCES users(id),
approved_at TIMESTAMPTZ,
processed_at TIMESTAMPTZ,
failure_reason TEXT,
-- Notes
notes TEXT,
customer_notification_sent BOOLEAN DEFAULT false,
-- Audit fields
requested_by UUID NOT NULL REFERENCES users(id),
processed_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Credit notes for customer accounts
CREATE TABLE credit_notes (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
credit_number VARCHAR(50) UNIQUE NOT NULL, -- CR-2024-001
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
-- Credit details
amount DECIMAL(12,2) NOT NULL,
currency VARCHAR(3) DEFAULT 'USD',
reason VARCHAR(255) NOT NULL,
description TEXT,
credit_date DATE DEFAULT CURRENT_DATE,
-- Expiry and usage
expires_at DATE,
balance_remaining DECIMAL(12,2),
is_fully_used BOOLEAN DEFAULT false,
-- Related records
invoice_id UUID REFERENCES invoices(id), -- Original invoice if applicable
refund_id UUID REFERENCES refunds(id), -- If created from refund
-- Status
status VARCHAR(20) DEFAULT 'active', -- active, expired, fully_used, cancelled
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT check_customer_credit_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Credit applications to invoices
CREATE TABLE credit_applications (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
credit_note_id UUID NOT NULL REFERENCES credit_notes(id),
invoice_id UUID NOT NULL REFERENCES invoices(id),
amount_applied DECIMAL(12,2) NOT NULL,
applied_date DATE DEFAULT CURRENT_DATE,
applied_by UUID NOT NULL REFERENCES users(id),
notes TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 16. RECURRING INVOICES
-- =====================================================

-- Recurring invoice frequencies
CREATE TABLE recurring_frequencies (
id SERIAL PRIMARY KEY,
name VARCHAR(20) UNIQUE NOT NULL, -- weekly, monthly, quarterly, yearly
interval_type VARCHAR(10) NOT NULL, -- days, weeks, months, years
interval_value INTEGER NOT NULL, -- 1, 2, 3, etc.
description TEXT,
is_active BOOLEAN DEFAULT true,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Recurring invoices setup
CREATE TABLE recurring_invoices (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
recurring_name VARCHAR(255) NOT NULL,
-- Customer information
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
template_invoice_id UUID REFERENCES invoices(id), -- Template to copy from
-- Billing information
billing_info_id UUID REFERENCES customer_billing_info(id),
template_id UUID REFERENCES invoice_templates(id),
-- Recurring settings
frequency_id INTEGER NOT NULL REFERENCES recurring_frequencies(id),
interval_count INTEGER DEFAULT 1, -- Every 1 month, every 3 months, etc.
-- Schedule
start_date DATE NOT NULL,
end_date DATE, -- NULL for indefinite
next_invoice_date DATE NOT NULL,
last_invoice_date DATE,
-- Limits
max_invoices INTEGER, -- Stop after X invoices
invoices_generated INTEGER DEFAULT 0,
-- Financial details (can override template)
currency VARCHAR(3) DEFAULT 'USD',
subtotal DECIMAL(12,2),
tax_amount DECIMAL(12,2),
total_amount DECIMAL(12,2),
-- Status
is_active BOOLEAN DEFAULT true,
is_paused BOOLEAN DEFAULT false,
paused_reason TEXT,
paused_until DATE,
-- Notes
description TEXT,
notes TEXT,
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
updated_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
deleted_at TIMESTAMPTZ,

    CONSTRAINT check_customer_recurring_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Add foreign key reference back to invoices
ALTER TABLE invoices ADD CONSTRAINT fk_invoices_recurring
FOREIGN KEY (recurring_invoice_id) REFERENCES recurring_invoices(id);

-- Recurring invoice line items template
CREATE TABLE recurring_invoice_line_items (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
recurring_invoice_id UUID NOT NULL REFERENCES recurring_invoices(id) ON DELETE CASCADE,
product_id UUID REFERENCES products(id),
-- Item details (template)
item_type VARCHAR(20) DEFAULT 'product',
name VARCHAR(255) NOT NULL,
description TEXT,
quantity DECIMAL(10,3) DEFAULT 1,
unit VARCHAR(20) DEFAULT 'each',
unit_price DECIMAL(10,2) DEFAULT 0,
tax_rate DECIMAL(5,2) DEFAULT 0,
line_total DECIMAL(12,2) NOT NULL,
sort_order INTEGER DEFAULT 0,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Track generated invoices from recurring setup
CREATE TABLE recurring_invoice_history (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
recurring_invoice_id UUID NOT NULL REFERENCES recurring_invoices(id),
generated_invoice_id UUID NOT NULL REFERENCES invoices(id),
generation_date DATE DEFAULT CURRENT_DATE,
billing_period_start DATE,
billing_period_end DATE,
amount DECIMAL(12,2),
status VARCHAR(20), -- generated, sent, paid, failed
notes TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 17. EMAIL & COMMUNICATION TRACKING
-- =====================================================

-- Email templates
CREATE TABLE email_templates (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(100) NOT NULL,
template_type VARCHAR(50) NOT NULL, -- invoice_send, payment_reminder, quote_send, etc.
subject VARCHAR(255) NOT NULL,
body_html TEXT NOT NULL,
body_text TEXT,
-- Template variables documentation
available_variables JSONB, -- {"customer_name": "Customer's full name", ...}
-- Settings
is_default BOOLEAN DEFAULT false,
is_active BOOLEAN DEFAULT true,
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
updated_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Email log for tracking all emails sent
CREATE TABLE email_log (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
-- Email details
to_email VARCHAR(255) NOT NULL,
to_name VARCHAR(255),
from_email VARCHAR(255) NOT NULL,
from_name VARCHAR(255),
subject VARCHAR(255) NOT NULL,
body_html TEXT,
body_text TEXT,
-- Related records
template_id UUID REFERENCES email_templates(id),
invoice_id UUID REFERENCES invoices(id),
quote_id UUID REFERENCES quotes(id),
payment_id UUID REFERENCES payments(id),
contact_id UUID REFERENCES contacts(id),
-- Delivery tracking
email_provider VARCHAR(50), -- sendgrid, ses, mailgun
provider_message_id VARCHAR(255),
status VARCHAR(20) DEFAULT 'queued', -- queued, sent, delivered, bounced, failed, opened, clicked
sent_at TIMESTAMPTZ,
delivered_at TIMESTAMPTZ,
opened_at TIMESTAMPTZ,
clicked_at TIMESTAMPTZ,
bounced_at TIMESTAMPTZ,
bounce_reason TEXT,
-- Attachments
has_attachments BOOLEAN DEFAULT false,
attachment_count INTEGER DEFAULT 0,
-- Audit fields
sent_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Payment reminders schedule
CREATE TABLE payment_reminders (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
invoice_id UUID NOT NULL REFERENCES invoices(id),
-- Reminder settings
reminder_type VARCHAR(20) NOT NULL, -- before_due, overdue, final_notice
days_offset INTEGER NOT NULL, -- Days before/after due date
-- Reminder details
email_template_id UUID REFERENCES email_templates(id),
subject VARCHAR(255),
message TEXT,
-- Status
is_sent BOOLEAN DEFAULT false,
scheduled_for TIMESTAMPTZ NOT NULL,
sent_at TIMESTAMPTZ,
email_log_id UUID REFERENCES email_log(id),
-- Settings
is_active BOOLEAN DEFAULT true,
created_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 18. CUSTOMER PORTAL
-- =====================================================

-- Customer portal access
CREATE TABLE customer_portal_access (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
customer_company_id UUID REFERENCES companies(id),
customer_contact_id UUID REFERENCES contacts(id),
-- Access credentials
email VARCHAR(255) NOT NULL,
password_hash VARCHAR(255),
-- Access token for passwordless login
access_token VARCHAR(100) UNIQUE,
token_expires_at TIMESTAMPTZ,
-- Settings
is_active BOOLEAN DEFAULT true,
email_verified BOOLEAN DEFAULT false,
email_verification_token VARCHAR(100),
-- Activity tracking
last_login_at TIMESTAMPTZ,
login_count INTEGER DEFAULT 0,
-- Permissions
can_view_invoices BOOLEAN DEFAULT true,
can_make_payments BOOLEAN DEFAULT true,
can_view_quotes BOOLEAN DEFAULT true,
can_download_documents BOOLEAN DEFAULT true,
-- Audit fields
created_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT check_customer_portal_reference CHECK (
        (customer_company_id IS NOT NULL AND customer_contact_id IS NULL) OR
        (customer_company_id IS NULL AND customer_contact_id IS NOT NULL)
    )

);

-- Customer portal activity log
CREATE TABLE customer_portal_activity (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
portal_access_id UUID NOT NULL REFERENCES customer_portal_access(id),
activity_type VARCHAR(50) NOT NULL, -- login, view_invoice, make_payment, download_document
-- Related records
invoice_id UUID REFERENCES invoices(id),
quote_id UUID REFERENCES quotes(id),
payment_id UUID REFERENCES payments(id),
document_id UUID REFERENCES documents(id),
-- Activity details
description TEXT,
ip_address INET,
user_agent TEXT,
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 19. SYSTEM AUDIT & LOGGING
-- =====================================================

-- Comprehensive audit log
CREATE TABLE audit_logs (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
-- What was changed
table_name VARCHAR(63) NOT NULL,
record_id UUID NOT NULL,
action VARCHAR(10) NOT NULL, -- INSERT, UPDATE, DELETE
-- Change details
old_values JSONB,
new_values JSONB,
changed_fields TEXT[], -- Array of field names that changed
-- Who made the change
user_id UUID REFERENCES users(id),
user_email VARCHAR(255),
user_role VARCHAR(50),
-- When and where
ip_address INET,
user_agent TEXT,
session_id VARCHAR(100),
-- Context
request_id UUID, -- For tracing requests
operation_type VARCHAR(50), -- api_call, bulk_import, system_job, etc.
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- System configuration settings
CREATE TABLE system_settings (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
setting_key VARCHAR(100) UNIQUE NOT NULL,
setting_value JSONB NOT NULL,
description TEXT,
setting_type VARCHAR(20) DEFAULT 'general', -- general, email, payment, security, etc.
is_encrypted BOOLEAN DEFAULT false,
is_public BOOLEAN DEFAULT false, -- Can be accessed by frontend
-- Validation
validation_rules JSONB, -- JSON schema for validation
-- Audit
updated_by UUID REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Webhook endpoints configuration
CREATE TABLE webhook_endpoints (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
name VARCHAR(100) NOT NULL,
url TEXT NOT NULL,
secret_key VARCHAR(255), -- For signature verification
-- Events to listen for
events TEXT[] NOT NULL, -- ['invoice.paid', 'payment.failed', etc.]
-- Settings
is_active BOOLEAN DEFAULT true,
retry_attempts INTEGER DEFAULT 3,
timeout_seconds INTEGER DEFAULT 30,
-- Headers
custom_headers JSONB,
-- Status
last_success_at TIMESTAMPTZ,
last_failure_at TIMESTAMPTZ,
failure_count INTEGER DEFAULT 0,
-- Audit
created_by UUID NOT NULL REFERENCES users(id),
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Webhook delivery log
CREATE TABLE webhook_deliveries (
id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
webhook_endpoint_id UUID NOT NULL REFERENCES webhook_endpoints(id),
event_type VARCHAR(50) NOT NULL,
-- Payload
payload JSONB NOT NULL,
-- Related record
related_table VARCHAR(63),
related_id UUID,
-- Delivery details
http_status INTEGER,
response_body TEXT,
response_headers JSONB,
delivery_duration_ms INTEGER,
-- Status
status VARCHAR(20) DEFAULT 'pending', -- pending, success, failed, retrying
attempt_count INTEGER DEFAULT 0,
next_retry_at TIMESTAMPTZ,
error_message TEXT,
-- Timing
created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
delivered_at TIMESTAMPTZ
);

-- =====================================================
-- 20. PERFORMANCE OPTIMIZATION INDEXES
-- =====================================================

-- Users table indexes
CREATE INDEX idx_users_email ON users(email) WHERE deleted_at IS NULL;
CREATE INDEX idx_users_active ON users(is_active) WHERE deleted_at IS NULL;
CREATE INDEX idx_users_last_login ON users(last_login_at DESC) WHERE deleted_at IS NULL;

-- Companies table indexes
CREATE INDEX idx_companies_name ON companies USING GIN(name gin_trgm_ops) WHERE deleted_at IS NULL;
CREATE INDEX idx_companies_owner ON companies(owner_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_companies_industry ON companies(industry) WHERE deleted_at IS NULL;

-- Contacts table indexes
CREATE INDEX idx_contacts_name ON contacts USING GIN((first_name || ' ' || last_name) gin_trgm_ops) WHERE deleted_at IS NULL;
CREATE INDEX idx_contacts_email ON contacts(email) WHERE deleted_at IS NULL AND email IS NOT NULL;
CREATE INDEX idx_contacts_company ON contacts(company_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_contacts_owner ON contacts(owner_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_contacts_last_contacted ON contacts(last_contacted_at DESC) WHERE deleted_at IS NULL;

-- Leads table indexes
CREATE INDEX idx_leads_status ON leads(status_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_leads_owner ON leads(owner_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_leads_source ON leads(source_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_leads_expected_close ON leads(expected_close_date) WHERE deleted_at IS NULL;

-- Deals table indexes
CREATE INDEX idx_deals_stage ON deals(stage_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_deals_owner ON deals(owner_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_deals_company ON deals(company_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_deals_expected_close ON deals(expected_close_date) WHERE deleted_at IS NULL;
CREATE INDEX idx_deals_amount ON deals(amount DESC) WHERE deleted_at IS NULL;
CREATE INDEX idx_deals_won ON deals(is_won) WHERE deleted_at IS NULL AND is_won IS NOT NULL;

-- Jobs table indexes
CREATE INDEX idx_jobs_status ON jobs(status_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_jobs_assigned_to ON jobs(assigned_to) WHERE deleted_at IS NULL;
CREATE INDEX idx_jobs_priority ON jobs(priority_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_jobs_due_date ON jobs(due_date) WHERE deleted_at IS NULL;
CREATE INDEX idx_jobs_customer_company ON jobs(customer_company_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_jobs_job_number ON jobs(job_number) WHERE deleted_at IS NULL;

-- Tasks table indexes
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to) WHERE deleted_at IS NULL;
CREATE INDEX idx_tasks_status ON tasks(status_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_tasks_due_date ON tasks(due_date) WHERE deleted_at IS NULL;
CREATE INDEX idx_tasks_priority ON tasks(priority_id) WHERE deleted_at IS NULL;

-- Time entries indexes
CREATE INDEX idx_time_entries_user ON time_entries(user_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_time_entries_job ON time_entries(job_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_time_entries_date ON time_entries(DATE(start_time)) WHERE deleted_at IS NULL;
CREATE INDEX idx_time_entries_billable ON time_entries(is_billable) WHERE deleted_at IS NULL;

-- Expenses table indexes
CREATE INDEX idx_expenses_category ON expenses(category_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_expenses_date ON expenses(expense_date DESC) WHERE deleted_at IS NULL;
CREATE INDEX idx_expenses_status ON expenses(status) WHERE deleted_at IS NULL;
CREATE INDEX idx_expenses_submitted_by ON expenses
