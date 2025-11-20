FROM node:18-alpine

WORKDIR /app

# Instalar dependências do sistema
RUN apk add --no-cache git

# Copiar arquivos de dependências
COPY package*.json ./

# Instalar dependências
RUN npm ci --only=production

# Copiar código fonte
COPY . .

# Criar diretórios necessários
RUN mkdir -p /app/uploads /app/packages /app/logs

# Configurar usuário não-root
RUN addgroup -g 1001 -S nodejs
RUN adduser -S dryad -u 1001
RUN chown -R dryad:nodejs /app
USER dryad

# Expor porta
EXPOSE 4000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node healthcheck.js

# Comando de inicialização
CMD ["npm", "start"]