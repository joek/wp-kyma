#!/bin/bash


# Generate Certificate
openssl genrsa -out mkt-cloud.key 2048

# Call Application Connection URL
curl ${1} >  signingRequests.json

# Extract Subject data from signingrequest info and adjust it to the standard
CSR_SUB=$(cat signingRequests.json | jq '.certificate.subject' | sed 's/,/\//g' | sed 's/^/\//' | sed 's/"//g')
#  (certificat.subject) /OU=OrgUnit/O=Organization/L=Waldorf/ST=Waldorf/C=DE/CN=wp-joek

# Extract Signing URL
CSR_URL=$(cat signingRequests.json | jq '.csrUrl' | sed 's/"//g')
# (csrUrl) https://connector-service.wordpress.cluster.int.faros.kyma.cx/v1/applications/certificates?token=5Tn5kGEA9asFCRLPb_Sy6ejYhMrFXHToDGFE2165Z2bUbRH8oExPQvhrvjnN0AbTCOWACYjHhxXRhNcU0M0ENw==

# Create CSR using subject from signing request
openssl req -new -sha256 -out mkt-cloud.csr -key mkt-cloud.key -subj ${CSR_SUB}
 

export KYMA_CSR=$(openssl base64 -in mkt-cloud.csr | tr -d '\n')

curl --header "Content-Type: application/json" \
  --request POST \
  --data "{\"csr\":\"${KYMA_CSR}\"}"  \
  ${CSR_URL} \
   >  mkt-cloud.crt.json
 
 
cat mkt-cloud.crt.json | jq '.crt' | tr -d '\"' | base64 --decode > mkt-cloud.crt
